<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use ZipArchive;

class BackupSystemCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'spv:backup 
                            {--only-db : Backup database saja} 
                            {--only-files : Backup folder evidence foto/storage saja}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup database (PostgreSQL/MySQL) dan file evidence storage ke storage/NAS internal';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $startTime = microtime(true);
        $timestamp = date('Ymd_His');

        $onlyDb = $this->option('only-db');
        $onlyFiles = $this->option('only-files');

        $this->info("=======================================================");
        $this->info("   SPV-TRACK AUTOMATED BACKUP & DISASTER RECOVERY      ");
        $this->info("=======================================================");
        $this->line("Waktu Mulai : " . date('Y-m-d H:i:s'));

        // Direktori tujuan backup (bisa local path atau mount path NAS dari .env)
        $backupDir = env('BACKUP_PATH') ?: storage_path('app/backups');
        $backupDir = rtrim($backupDir, '/\\');

        if (!is_dir($backupDir)) {
            if (!mkdir($backupDir, 0755, true) && !is_dir($backupDir)) {
                $this->error("Gagal membuat direktori backup: {$backupDir}");
                Log::error("[SPV-BACKUP] Gagal membuat direktori backup: {$backupDir}");
                return 1;
            }
        }

        $this->line("Target Path : {$backupDir}");
        $results = [];

        // 1. BACKUP DATABASE
        if (!$onlyFiles) {
            $this->info("\n[1/3] Memproses Backup Database...");
            $dbResult = $this->backupDatabase($backupDir, $timestamp);
            $results[] = $dbResult;
        }

        // 2. BACKUP STORAGE FILES / EVIDENCE
        if (!$onlyDb) {
            $this->info("\n[2/3] Memproses Backup Storage Evidence & Dokumen...");
            $filesResult = $this->backupStorageFiles($backupDir, $timestamp);
            $results[] = $filesResult;
        }

        // 3. RETENTION POLICY (CLEANUP BACKUP LAMA)
        $this->info("\n[3/3] Memeriksa Kebijakan Retensi Backup Lama...");
        $retentionDays = (int) (env('BACKUP_RETENTION_DAYS', 14) ?: 14);
        $deletedCount = $this->pruneOldBackups($backupDir, $retentionDays);
        $this->line("Retensi: {$retentionDays} hari. File kadaluarsa dibersihkan: {$deletedCount} file.");

        // SUMMARY TABLE
        $this->newLine();
        $this->table(['Komponen', 'File Backup', 'Ukuran', 'Status'], $results);

        $duration = round(microtime(true) - $startTime, 2);
        $this->info("\nProses backup selesai dalam {$duration} detik.");

        Log::info("[SPV-BACKUP] Backup berhasil dijalankan dalam {$duration}s", [
            'results' => $results,
            'backup_dir' => $backupDir,
        ]);

        return 0;
    }

    /**
     * Backup database PostgreSQL atau MySQL
     */
    protected function backupDatabase(string $backupDir, string $timestamp): array
    {
        $connection = config('database.default', 'pgsql');
        $dbConfig = config("database.connections.{$connection}", []);

        $database = $dbConfig['database'] ?? 'spv_track';
        $host = $dbConfig['host'] ?? '127.0.0.1';
        $port = $dbConfig['port'] ?? ($connection === 'pgsql' ? 5432 : 3306);
        $username = $dbConfig['username'] ?? 'postgres';
        $password = (string) ($dbConfig['password'] ?? '');

        $sqlFilename = "db_{$database}_{$timestamp}.sql";
        $sqlPath = $backupDir . DIRECTORY_SEPARATOR . $sqlFilename;
        $gzFilename = "{$sqlFilename}.gz";
        $gzPath = $backupDir . DIRECTORY_SEPARATOR . $gzFilename;

        $success = false;

        if ($connection === 'pgsql') {
            $pgDump = $this->findPgDumpBinary();
            if ($pgDump) {
                $this->line("Menggunakan pg_dump CLI: {$pgDump}");
                $cmd = [
                    $pgDump,
                    '-h', $host,
                    '-p', (string) $port,
                    '-U', $username,
                    '-d', $database,
                    '-F', 'p',
                    '-f', $sqlPath,
                ];

                $process = new Process($cmd, null, ['PGPASSWORD' => $password]);
                $process->setTimeout(300);
                $process->run();

                if ($process->isSuccessful() && file_exists($sqlPath) && filesize($sqlPath) > 0) {
                    $success = true;
                } else {
                    $this->warn("pg_dump menghasilkan error: " . $process->getErrorOutput() . ". Mengalihkan ke PHP PDO dumper...");
                }
            } else {
                $this->line("pg_dump tidak ditemukan di PATH, menggunakan PHP PDO fallback...");
            }
        } elseif ($connection === 'mysql') {
            $mysqldump = $this->findMysqldumpBinary();
            if ($mysqldump) {
                $cmd = [
                    $mysqldump,
                    "-h{$host}",
                    "-P{$port}",
                    "-u{$username}",
                    $database,
                    "--result-file={$sqlPath}",
                ];
                if ($password !== '') {
                    $cmd[] = "-p{$password}";
                }
                $process = new Process($cmd);
                $process->setTimeout(300);
                $process->run();

                if ($process->isSuccessful() && file_exists($sqlPath) && filesize($sqlPath) > 0) {
                    $success = true;
                }
            }
        }

        // Fallback jika pg_dump / mysqldump CLI gagal
        if (!$success) {
            $success = $this->fallbackPdoDump($sqlPath);
        }

        if ($success && file_exists($sqlPath)) {
            // Kompres SQL ke GZIP (.sql.gz)
            if (function_exists('gzencode')) {
                $content = file_get_contents($sqlPath);
                file_put_contents($gzPath, gzencode($content, 9));
                @unlink($sqlPath);
                $finalFile = $gzFilename;
                $finalSize = $this->formatBytes(filesize($gzPath));
            } else {
                $finalFile = $sqlFilename;
                $finalSize = $this->formatBytes(filesize($sqlPath));
            }

            return ['Database (' . strtoupper($connection) . ')', $finalFile, $finalSize, 'BERHASIL'];
        }

        return ['Database (' . strtoupper($connection) . ')', '-', '0 B', 'GAGAL'];
    }

    /**
     * Backup folder storage evidence dan shipments
     */
    protected function backupStorageFiles(string $backupDir, string $timestamp): array
    {
        $sourcePath = storage_path('app/public/shipments');
        $zipFilename = "files_spv_evidence_{$timestamp}.zip";
        $zipPath = $backupDir . DIRECTORY_SEPARATOR . $zipFilename;

        if (!is_dir($sourcePath)) {
            return ['Storage Evidence', '-', '0 B', 'DILEWATI (Folder Kosong)'];
        }

        if (!class_exists('ZipArchive')) {
            $this->warn("Ekstensi PHP ZipArchive tidak aktif. Lewati kompresi file evidence.");
            return ['Storage Evidence', '-', '0 B', 'GAGAL (ZipArchive extension disabled)'];
        }

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return ['Storage Evidence', '-', '0 B', 'GAGAL (Gagal membuat file zip)'];
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($sourcePath, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        $fileCount = 0;
        foreach ($files as $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = 'shipments/' . substr($filePath, strlen($sourcePath) + 1);
                $relativePath = str_replace('\\', '/', $relativePath);
                $zip->addFile($filePath, $relativePath);
                $fileCount++;
            }
        }

        $zip->close();

        if (file_exists($zipPath)) {
            $size = $this->formatBytes(filesize($zipPath));
            $this->line("Berhasil mengompres {$fileCount} file evidence ke {$zipFilename}");
            return ['Storage Evidence (' . $fileCount . ' files)', $zipFilename, $size, 'BERHASIL'];
        }

        return ['Storage Evidence', '-', '0 B', 'GAGAL'];
    }

    /**
     * Hapus backup yang lebih tua dari retensi (hari)
     */
    protected function pruneOldBackups(string $backupDir, int $days): int
    {
        $deleted = 0;
        $threshold = time() - ($days * 86400);

        $files = glob($backupDir . DIRECTORY_SEPARATOR . '{db_,files_}*.*', GLOB_BRACE);
        if (!$files) {
            return 0;
        }

        foreach ($files as $file) {
            if (is_file($file) && filemtime($file) < $threshold) {
                @unlink($file);
                $deleted++;
            }
        }

        return $deleted;
    }

    /**
     * Cari binary pg_dump di server Linux/Windows
     */
    protected function findPgDumpBinary(): ?string
    {
        if ($custom = env('PG_DUMP_PATH')) {
            if (file_exists($custom)) return $custom;
        }

        $candidates = [
            'pg_dump',
            '/usr/bin/pg_dump',
            '/usr/local/bin/pg_dump',
            'C:\\Program Files\\PostgreSQL\\17\\bin\\pg_dump.exe',
            'C:\\Program Files\\PostgreSQL\\16\\bin\\pg_dump.exe',
            'C:\\Program Files\\PostgreSQL\\15\\bin\\pg_dump.exe',
            'C:\\laragon\\bin\\postgresql\\bin\\pg_dump.exe',
        ];

        foreach ($candidates as $bin) {
            if (@file_exists($bin) || $this->commandExists($bin)) {
                return $bin;
            }
        }

        return null;
    }

    /**
     * Cari binary mysqldump di server Linux/Windows
     */
    protected function findMysqldumpBinary(): ?string
    {
        if ($custom = env('MYSQL_DUMP_PATH')) {
            if (file_exists($custom)) return $custom;
        }

        $candidates = [
            'mysqldump',
            '/usr/bin/mysqldump',
            'C:\\laragon\\bin\\mysql\\current\\bin\\mysqldump.exe',
        ];

        foreach ($candidates as $bin) {
            if (@file_exists($bin) || $this->commandExists($bin)) {
                return $bin;
            }
        }

        return null;
    }

    /**
     * Cek apakah command cli dapat dijalankan
     */
    protected function commandExists(string $cmd): bool
    {
        $where = PHP_OS_FAMILY === 'Windows' ? 'where' : 'which';
        $process = new Process([$where, $cmd]);
        $process->run();
        return $process->isSuccessful();
    }

    /**
     * Fallback dumper via PHP PDO jika pg_dump / mysqldump CLI tidak terpasang
     */
    protected function fallbackPdoDump(string $sqlPath): bool
    {
        try {
            $pdo = DB::connection()->getPdo();
            $tables = DB::connection()->getDoctrineSchemaManager()->listTableNames();

            $handle = fopen($sqlPath, 'w');
            fwrite($handle, "-- SPV-TRACK PDO FALLBACK DUMP\n-- Date: " . date('Y-m-d H:i:s') . "\n\n");

            foreach ($tables as $table) {
                $rows = DB::table($table)->get();
                if ($rows->isEmpty()) continue;

                fwrite($handle, "-- Data for table: {$table}\n");
                foreach ($rows as $row) {
                    $data = (array) $row;
                    $cols = array_map(fn($c) => '"' . $c . '"', array_keys($data));
                    $vals = array_map(function ($val) use ($pdo) {
                        return $val === null ? 'NULL' : $pdo->quote($val);
                    }, array_values($data));

                    $insert = sprintf(
                        "INSERT INTO \"%s\" (%s) VALUES (%s);\n",
                        $table,
                        implode(', ', $cols),
                        implode(', ', $vals)
                    );
                    fwrite($handle, $insert);
                }
                fwrite($handle, "\n");
            }

            fclose($handle);
            return true;
        } catch (\Throwable $e) {
            $this->error("PDO fallback dump gagal: " . $e->getMessage());
            Log::error("[SPV-BACKUP] PDO fallback dump error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Format bytes to human readable
     */
    protected function formatBytes(int $bytes): string
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        }
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' B';
    }
}
