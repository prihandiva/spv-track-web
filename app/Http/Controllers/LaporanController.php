<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\Shipment;
use App\Models\SopPhotoPoint;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaporanController extends Controller
{
    /**
     * Tampilkan halaman utama Laporan & Audit Staging SPV-Track.
     */
    public function index(Request $request): View
    {
        $allPetugas = Karyawan::orderBy('nama')->get();
        $allShipments = $this->getEnrichedShipments($request);

        // Ekstraksi Tab Aktif (rekap, audit, petugas)
        $activeTab = $request->input('tab', 'rekap');
        if (! in_array($activeTab, ['rekap', 'audit', 'petugas'], true)) {
            $activeTab = 'rekap';
        }

        // Hitung Ringkasan KPI Eksekutif berdasarkan data terfilter
        $summary = $this->calculateSummaryMetrics($allShipments);

        // Filter kepatuhan jika diminta
        $filteredForTab = $allShipments;
        if ($compliance = $request->input('compliance')) {
            if ($compliance === 'lengkap') {
                $filteredForTab = $filteredForTab->where('is_complete', true)->values();
            } elseif ($compliance === 'kurang') {
                $filteredForTab = $filteredForTab->where('is_complete', false)->values();
            }
        }

        // Paginasi untuk Tab 1 (Rekapitulasi Pengiriman)
        $perPage = 15;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentItems = $filteredForTab->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $paginatedShipments = new LengthAwarePaginator(
            $currentItems,
            $filteredForTab->count(),
            $perPage,
            $currentPage,
            ['path' => LengthAwarePaginator::resolveCurrentPath(), 'query' => $request->query()]
        );

        // Data untuk Tab 2 (Audit Kepatuhan 27 Titik SOP)
        $sopPoints = SopPhotoPoint::orderBy('urutan')->get();
        $sopAuditMatrix = $this->buildSopAuditMatrix($sopPoints, $allShipments);

        // Data untuk Tab 3 (Produktivitas Petugas)
        $petugasProductivity = $this->buildPetugasProductivity($allPetugas, $allShipments);

        // Label Periode untuk Tampilan
        $periodLabel = $this->generatePeriodLabel($request);

        return view('laporan.index', compact(
            'summary',
            'paginatedShipments',
            'sopAuditMatrix',
            'petugasProductivity',
            'allPetugas',
            'activeTab',
            'periodLabel'
        ));
    }

    /**
     * Ekspor data rekapitulasi ke format Excel (CSV kompatibel dengan UTF-8 BOM atau file .xls).
     */
    public function exportExcel(Request $request): StreamedResponse|Response
    {
        $shipments = $this->getEnrichedShipments($request);

        if ($compliance = $request->input('compliance')) {
            if ($compliance === 'lengkap') {
                $shipments = $shipments->where('is_complete', true)->values();
            } elseif ($compliance === 'kurang') {
                $shipments = $shipments->where('is_complete', false)->values();
            }
        }

        $format = $request->input('format', 'csv');
        $timestamp = now('Asia/Jakarta')->format('Ymd_His');
        $fileName = "Rekap_Laporan_SPV_Track_{$timestamp}";

        if ($format === 'xls') {
            $periodLabel = $this->generatePeriodLabel($request);
            $summary = $this->calculateSummaryMetrics($shipments);

            $content = view('laporan.export-xls', compact('shipments', 'periodLabel', 'summary'))->render();

            return response($content, 200, [
                'Content-Type' => 'application/vnd.ms-excel; charset=utf-8',
                'Content-Disposition' => "attachment; filename=\"{$fileName}.xls\"",
                'Pragma' => 'no-cache',
                'Expires' => '0',
            ]);
        }

        // Default: CSV dengan UTF-8 BOM untuk kompatibilitas penuh Excel Windows & Mac
        $response = new StreamedResponse(function () use ($shipments) {
            $handle = fopen('php://output', 'w');

            // Tambahkan UTF-8 BOM agar Excel mendeteksi encoding dengan benar
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // Baris Header CSV
            fputcsv($handle, [
                'No',
                'Tanggal Staging',
                'Waktu (Shift)',
                'Lokasi Warehouse',
                'Cuaca',
                'No. Packing List',
                'No. Container',
                'Plat Nomor',
                'Shipment No',
                'Shipment Group',
                'Jenis Produk',
                'Jenis Pengiriman',
                'Nama Sopir',
                'Forwarding Agent',
                'Tujuan Pengiriman',
                'Petugas Staging',
                'Kelengkapan Foto SOP (dari 27)',
                'Persentase Kepatuhan SOP (%)',
                'Status Kepatuhan',
                'Validasi OCR Container',
                'Status Dokumen',
                'Waktu Submit',
            ]);

            $index = 1;
            foreach ($shipments as $s) {
                $date = $s->tanggal_staging ? $s->tanggal_staging->format('d/m/Y') : ($s->created_at ? $s->created_at->format('d/m/Y') : '-');
                $submitTime = $s->submitted_at ? $s->submitted_at->format('d/m/Y H:i') : '-';
                $petugasNames = $s->allKaryawans()->pluck('nama')->join(', ') ?: ($s->karyawan?->nama ?? '-');

                fputcsv($handle, [
                    $index++,
                    $date,
                    ucfirst((string) $s->waktu),
                    ucfirst((string) $s->warehouse_lokasi),
                    ucfirst((string) $s->cuaca),
                    $s->packing_list_no ?? '-',
                    $s->nomor_container_atau_plat ?? '-',
                    $s->plat_nomor ?? '-',
                    $s->shipment_no ?? '-',
                    $s->shipment_group ?? '-',
                    strtoupper((string) $s->jenis_produk),
                    strtoupper((string) $s->jenis_pengiriman),
                    $s->nama_sopir ?? '-',
                    $s->agen_forwarding ?? '-',
                    $s->tujuan_pengiriman ?? '-',
                    $petugasNames,
                    "{$s->points_count} / 27",
                    "{$s->compliance_rate}%",
                    $s->is_complete ? 'Lengkap (100%)' : 'Belum Lengkap',
                    $s->ocr_label,
                    ucfirst((string) $s->status),
                    $submitTime,
                ]);
            }

            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', "attachment; filename=\"{$fileName}.csv\"");
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }

    /**
     * Tampilan pratinjau browser & cetak rekapitulasi Berita Acara Staging (Print Preview).
     */
    public function printRekap(Request $request): View
    {
        $shipments = $this->getEnrichedShipments($request);

        if ($compliance = $request->input('compliance')) {
            if ($compliance === 'lengkap') {
                $shipments = $shipments->where('is_complete', true)->values();
            } elseif ($compliance === 'kurang') {
                $shipments = $shipments->where('is_complete', false)->values();
            }
        }

        $summary = $this->calculateSummaryMetrics($shipments);
        $periodLabel = $this->generatePeriodLabel($request);

        $sopPoints = SopPhotoPoint::orderBy('urutan')->get();
        $sopAuditMatrix = $this->buildSopAuditMatrix($sopPoints, $shipments);

        return view('laporan.rekap-pdf', compact(
            'shipments',
            'summary',
            'periodLabel',
            'sopAuditMatrix'
        ) + ['isPdf' => false]);
    }

    /**
     * Unduh berkas rekapitulasi Berita Acara Staging dalam bentuk file PDF landscape.
     */
    public function downloadRekapPdf(Request $request): Response
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(180);

        $shipments = $this->getEnrichedShipments($request);

        if ($compliance = $request->input('compliance')) {
            if ($compliance === 'lengkap') {
                $shipments = $shipments->where('is_complete', true)->values();
            } elseif ($compliance === 'kurang') {
                $shipments = $shipments->where('is_complete', false)->values();
            }
        }

        $summary = $this->calculateSummaryMetrics($shipments);
        $periodLabel = $this->generatePeriodLabel($request);

        $sopPoints = SopPhotoPoint::orderBy('urutan')->get();
        $sopAuditMatrix = $this->buildSopAuditMatrix($sopPoints, $shipments);

        $pdf = Pdf::loadView('laporan.rekap-pdf', [
            'shipments' => $shipments,
            'summary' => $summary,
            'periodLabel' => $periodLabel,
            'sopAuditMatrix' => $sopAuditMatrix,
            'isPdf' => true,
        ])->setPaper('a4', 'landscape');

        $timestamp = now('Asia/Jakarta')->format('Ymd_His');
        $fileName = "Berita_Acara_Rekap_SPV_Track_{$timestamp}.pdf";

        return $pdf->download($fileName);
    }

    /**
     * Ambil data shipment sesuai filter, lengkap dengan relasi dan kalkulasi atribut titik SOP & OCR.
     */
    protected function getEnrichedShipments(Request $request): Collection
    {
        $query = Shipment::with([
            'karyawan',
            'karyawans',
            'evidenceItems.sopPhotoPoint',
            'evidenceItems.ocrResult',
            'photos',
            'user',
        ])->latest('created_at');

        // Filter Mode Periode Waktu
        $mode = $request->input('mode', 'all');

        if ($mode === 'harian') {
            $targetDate = $request->input('date');
            if (! $targetDate && $request->filled('year') && $request->filled('month') && $request->filled('day')) {
                $targetDate = sprintf('%04d-%02d-%02d', (int) $request->input('year'), (int) $request->input('month'), (int) $request->input('day'));
            }
            $targetDate = $targetDate ? Carbon::parse($targetDate)->toDateString() : now()->toDateString();

            $query->where(function ($q) use ($targetDate) {
                $q->whereDate('tanggal_staging', $targetDate)
                    ->orWhere(fn ($sq) => $sq->whereNull('tanggal_staging')->whereDate('created_at', $targetDate));
            });
        } elseif ($mode === 'mingguan' || $mode === 'range') {
            $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date'))->toDateString() : now()->startOfWeek()->toDateString();
            $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date'))->toDateString() : now()->endOfWeek()->toDateString();

            if ($startDate > $endDate) {
                [$startDate, $endDate] = [$endDate, $startDate];
            }

            $query->where(function ($q) use ($startDate, $endDate) {
                $q->where(function ($sq) use ($startDate, $endDate) {
                    $sq->whereDate('tanggal_staging', '>=', $startDate)
                        ->whereDate('tanggal_staging', '<=', $endDate);
                })->orWhere(function ($sq) use ($startDate, $endDate) {
                    $sq->whereNull('tanggal_staging')
                        ->whereDate('created_at', '>=', $startDate)
                        ->whereDate('created_at', '<=', $endDate);
                });
            });
        } elseif ($mode === 'bulanan') {
            $year = (int) ($request->input('year') ?: now()->year);
            $month = (int) ($request->input('month') ?: now()->month);

            $query->where(function ($q) use ($year, $month) {
                $q->where(function ($sq) use ($year, $month) {
                    $sq->whereYear('tanggal_staging', $year)
                        ->whereMonth('tanggal_staging', $month);
                })->orWhere(function ($sq) use ($year, $month) {
                    $sq->whereNull('tanggal_staging')
                        ->whereYear('created_at', $year)
                        ->whereMonth('created_at', $month);
                });
            });
        } elseif ($mode === 'tahunan') {
            $year = (int) ($request->input('year') ?: now()->year);

            $query->where(function ($q) use ($year) {
                $q->whereYear('tanggal_staging', $year)
                    ->orWhere(fn ($sq) => $sq->whereNull('tanggal_staging')->whereYear('created_at', $year));
            });
        }

        // Filter Jenis Produk
        $product = $request->input('jenis_produk');
        if ($product && in_array($product, ['fiber', 'sodium'], true)) {
            $query->where('jenis_produk', $product);
        }

        // Filter Jenis Pengiriman
        $type = $request->input('jenis_pengiriman');
        if ($type && in_array($type, ['export', 'lokal'], true)) {
            $query->where('jenis_pengiriman', $type);
        }

        // Filter Petugas Lapangan
        $karyawanId = $request->input('karyawan_id');
        if ($karyawanId && $karyawanId !== 'all') {
            $query->where(function ($q) use ($karyawanId) {
                $q->where('karyawan_id', $karyawanId)
                    ->orWhereHas('karyawans', fn ($sq) => $sq->where('karyawans.id', $karyawanId));
            });
        }

        // Filter Status Pengiriman
        $status = $request->input('status');
        if ($status && in_array($status, ['submitted', 'draft'], true)) {
            $query->where('status', $status);
        }

        // Pencarian Teks Bebas
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('packing_list_no', 'like', "%{$search}%")
                    ->orWhere('nomor_container_atau_plat', 'like', "%{$search}%")
                    ->orWhere('plat_nomor', 'like', "%{$search}%")
                    ->orWhere('nama_sopir', 'like', "%{$search}%")
                    ->orWhere('shipment_no', 'like', "%{$search}%")
                    ->orWhere('agen_forwarding', 'like', "%{$search}%");
            });
        }

        $shipments = $query->get();

        // Pengayaan Tiap Record Shipment dengan Kalkulasi Titik SOP & OCR
        return $shipments->map(function ($shipment) {
            $pointNumbers = collect();

            // Dari evidence_items
            foreach ($shipment->evidenceItems as $ev) {
                if ($ev->sopPhotoPoint && $ev->sopPhotoPoint->urutan) {
                    $pointNumbers->push($ev->sopPhotoPoint->urutan);
                }
            }

            // Dari shipment_photos
            foreach ($shipment->photos as $ph) {
                if ($ph->point_no && ! $ph->is_extra) {
                    $pointNumbers->push($ph->point_no);
                }
            }

            $uniquePoints = $pointNumbers->unique()->values();
            $pointsCount = $uniquePoints->count();

            $shipment->unique_points = $uniquePoints;
            $shipment->points_count = $pointsCount;
            $shipment->compliance_rate = round(($pointsCount / 27) * 100, 1);
            $shipment->is_complete = $pointsCount >= 27;

            // Evaluasi Status OCR
            $ocrMatch = false;
            $ocrChecked = false;

            foreach ($shipment->evidenceItems as $ev) {
                if ($ev->ocrResult) {
                    $ocrChecked = true;
                    if ($ev->ocrResult->cocok_dengan_input) {
                        $ocrMatch = true;
                        break;
                    }
                }
            }

            if (! $ocrMatch) {
                foreach ($shipment->photos as $ph) {
                    if ($ph->ocr_text) {
                        $ocrChecked = true;
                        $cont = strtolower(trim((string) $shipment->nomor_container_atau_plat));
                        if ($cont !== '' && str_contains(strtolower($ph->ocr_text), $cont)) {
                            $ocrMatch = true;
                            break;
                        }
                    }
                }
            }

            $shipment->ocr_checked = $ocrChecked;
            $shipment->ocr_match = $ocrMatch;
            $shipment->ocr_label = $ocrMatch ? 'Valid Cocok' : ($ocrChecked ? 'Mismatch / Warning' : 'Belum Di-scan');

            return $shipment;
        });
    }

    /**
     * Hitung metrik agregat KPI untuk ringkasan laporan eksekutif.
     */
    protected function calculateSummaryMetrics(Collection $shipments): object
    {
        $total = $shipments->count();
        $submitted = $shipments->where('status', 'submitted')->count();
        $draft = $shipments->where('status', 'draft')->count();
        $fiber = $shipments->where('jenis_produk', 'fiber')->count();
        $sodium = $shipments->where('jenis_produk', 'sodium')->count();
        $export = $shipments->where('jenis_pengiriman', 'export')->count();
        $lokal = $shipments->where('jenis_pengiriman', 'lokal')->count();

        $lengkap = $shipments->where('is_complete', true)->count();
        $kurang = $total - $lengkap;
        $avgCompliance = $total > 0 ? round($shipments->avg('compliance_rate'), 1) : 0;
        $ocrValid = $shipments->where('ocr_match', true)->count();

        // Karyawan unik yang bertugas
        $petugasIds = collect();
        foreach ($shipments as $s) {
            if ($s->karyawan_id) {
                $petugasIds->push($s->karyawan_id);
            }
            foreach ($s->karyawans as $k) {
                $petugasIds->push($k->id);
            }
        }
        $uniquePetugasCount = $petugasIds->unique()->count();

        return (object) [
            'total_shipments' => $total,
            'total_submitted' => $submitted,
            'total_draft' => $draft,
            'total_fiber' => $fiber,
            'total_sodium' => $sodium,
            'total_export' => $export,
            'total_lokal' => $lokal,
            'total_lengkap' => $lengkap,
            'total_kurang' => $kurang,
            'avg_compliance' => $avgCompliance,
            'ocr_valid' => $ocrValid,
            'unique_petugas_count' => $uniquePetugasCount,
        ];
    }

    /**
     * Bangun matriks kepatuhan 27 titik foto SOP.
     */
    protected function buildSopAuditMatrix(Collection $sopPoints, Collection $shipments): Collection
    {
        $totalShipments = $shipments->count();

        return $sopPoints->map(function ($point) use ($shipments, $totalShipments) {
            $coveredShipments = $shipments->filter(function ($s) use ($point) {
                return $s->unique_points->contains($point->urutan);
            });

            $coveredCount = $coveredShipments->count();
            $percentage = $totalShipments > 0 ? round(($coveredCount / $totalShipments) * 100, 1) : 0;

            $missingShipments = $shipments->filter(function ($s) use ($point) {
                return ! $s->unique_points->contains($point->urutan);
            })->values();

            return (object) [
                'point' => $point,
                'urutan' => $point->urutan,
                'nama_titik' => $point->nama_titik,
                'deskripsi' => $point->deskripsi,
                'tipe_item' => $point->tipe_item ?? 'foto',
                'covered_count' => $coveredCount,
                'percentage' => $percentage,
                'missing_count' => $totalShipments - $coveredCount,
                'missing_shipments' => $missingShipments,
            ];
        });
    }

    /**
     * Bangun rekap produktivitas dan kepatuhan petugas lapangan.
     */
    protected function buildPetugasProductivity(Collection $allPetugas, Collection $shipments): Collection
    {
        return $allPetugas->map(function ($petugas) use ($shipments) {
            $handled = $shipments->filter(function ($s) use ($petugas) {
                return $s->karyawan_id === $petugas->id || $s->karyawans->contains('id', $petugas->id);
            });

            $count = $handled->count();
            $completeCount = $handled->where('is_complete', true)->count();
            $avgCompliance = $count > 0 ? round($handled->avg('compliance_rate'), 1) : 0;
            $fiberCount = $handled->where('jenis_produk', 'fiber')->count();
            $sodiumCount = $handled->where('jenis_produk', 'sodium')->count();
            $exportCount = $handled->where('jenis_pengiriman', 'export')->count();
            $lokalCount = $handled->where('jenis_pengiriman', 'lokal')->count();

            return (object) [
                'petugas' => $petugas,
                'total_handled' => $count,
                'complete_count' => $completeCount,
                'avg_compliance' => $avgCompliance,
                'fiber_count' => $fiberCount,
                'sodium_count' => $sodiumCount,
                'export_count' => $exportCount,
                'lokal_count' => $lokalCount,
            ];
        })->sortByDesc('total_handled')->values();
    }

    /**
     * Hasilkan label string yang manusiawi dan deskriptif untuk periode yang dipilih.
     */
    protected function generatePeriodLabel(Request $request): string
    {
        $mode = $request->input('mode', 'all');

        $indonesianMonths = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        if ($mode === 'harian') {
            $targetDate = $request->input('date') ?: now()->toDateString();
            $c = Carbon::parse($targetDate);

            return 'Harian: '.$c->locale('id')->isoFormat('dddd, DD MMMM YYYY');
        }

        if ($mode === 'mingguan' || $mode === 'range') {
            $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date'))->format('d M Y') : now()->startOfWeek()->format('d M Y');
            $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date'))->format('d M Y') : now()->endOfWeek()->format('d M Y');

            return "Rentang: {$startDate} s/d {$endDate}";
        }

        if ($mode === 'bulanan') {
            $year = (int) ($request->input('year') ?: now()->year);
            $month = (int) ($request->input('month') ?: now()->month);
            $mName = $indonesianMonths[$month] ?? "Bulan {$month}";

            return "Bulanan: {$mName} {$year}";
        }

        if ($mode === 'tahunan') {
            $year = (int) ($request->input('year') ?: now()->year);

            return "Tahunan: Tahun {$year}";
        }

        return 'Semua Data Tersedia';
    }
}
