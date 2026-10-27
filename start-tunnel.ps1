# Script otomatis untuk menjalankan Laravel + Cloudflare Tunnel
Clear-Host
Write-Host "==========================================================" -ForegroundColor Cyan
Write-Host "        SPV-TRACK - SISTEM AKSES MOBILE UNTUK HP          " -ForegroundColor Cyan
Write-Host "==========================================================" -ForegroundColor Cyan
Write-Host ""

# 1. Pastikan php artisan serve berjalan
$port8000 = Get-NetTCPConnection -State Listen -LocalPort 8000 -ErrorAction SilentlyContinue
if (-not $port8000) {
    Write-Host "[1/2] Menyalakan server Laravel di background..." -ForegroundColor Yellow
    Start-Process -FilePath "php" -ArgumentList "artisan serve --host=0.0.0.0 --port=8000" -WindowStyle Hidden
    Start-Sleep -Seconds 2
} else {
    Write-Host "[1/2] Server Laravel sudah aktif di port 8000 [OK]" -ForegroundColor Green
}

# 2. Hapus log tunnel lama
if (Test-Path "tunnel.log") { Remove-Item "tunnel.log" -Force }

# 3. Jalankan Cloudflare Tunnel
Write-Host "[2/2] Menghubungkan ke Cloudflare Network..." -ForegroundColor Yellow
$cloudflared = Start-Process -FilePath ".\cloudflared.exe" -ArgumentList "tunnel --protocol http2 --url http://127.0.0.1:8000" -RedirectStandardError "tunnel.log" -PassThru

$foundUrl = $null
$attempts = 0
while ($attempts -lt 25) {
    Start-Sleep -Seconds 1
    $attempts++
    if (Test-Path "tunnel.log") {
        $content = Get-Content "tunnel.log" -Raw -ErrorAction SilentlyContinue
        if ($content -match '(https://[a-zA-Z0-9-]+\.trycloudflare\.com)') {
            $foundUrl = $matches[1]
            break
        }
    }
}

if ($foundUrl) {
    Write-Host ""
    Write-Host "==========================================================" -ForegroundColor Green
    Write-Host " LINK WEBSITE UNTUK DIBUKA DI HP:" -ForegroundColor Green
    Write-Host " $foundUrl" -ForegroundColor Yellow -BackgroundColor Black
    Write-Host "==========================================================" -ForegroundColor Green
    Write-Host " (Link juga sudah disimpan otomatis di file: link-hp.txt)" -ForegroundColor Gray
    Write-Host ""
    
    # Simpan ke link-hp.txt
    Set-Content -Path "link-hp.txt" -Value $foundUrl
    
    Write-Host "Status: TERHUBUNG AKTIF." -ForegroundColor Cyan
    Write-Host "Tekan CTRL + C atau tutup jendela ini jika ingin mematikan." -ForegroundColor Gray
    
    # Tunggu proses
    $cloudflared.WaitForExit()
} else {
    Write-Host "Gagal mendeteksi link otomatis, silakan cek isi file tunnel.log" -ForegroundColor Red
}
