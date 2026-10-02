# Panduan Integrasi Modul Evidence Foto Flutter SPV-Track

Folder ini berisi implementasi lengkap client mobile Flutter untuk fitur penyimpanan foto evidence container/truck per shipment SPV-Track.

## 1. Dependensi (`pubspec.yaml`)
Tambahkan package open-source berikut ke file `pubspec.yaml` Flutter:
```yaml
dependencies:
  flutter:
    sdk: flutter
  http: ^1.2.0
  image_picker: ^1.0.7
```

## 2. Struktur File
- `models/shipment_photo_model.dart`: Data model `ShipmentPhotoItem` dan state upload (idle, uploading, success, error).
- `services/shipment_photo_api_service.dart`: Service komunikasi REST API (upload sementara multi-part dengan progress callback, submit final, jam server).
- `widgets/timeline_photo_card.dart`: Widget card titik per timeline dengan thumbnail, preview waktu WIB server, progress non-blocking, tombol kamera/galeri, ambil ulang, dan zoom.
- `widgets/photo_zoom_viewer.dart`: Viewer layar penuh dengan pinch-to-zoom, double-tap zoom, swipe horizontal antar foto dalam shipment, serta tombol toggle stempel WIB vs foto asli.
- `widgets/add_extra_photo_dialog.dart`: Dialog input nama/label foto ekstra saat menekan tombol (+).
- `pages/shipment_timeline_page.dart`: Halaman utama timeline inspeksi 27 titik + tombol floating (+) foto ekstra.
- `pages/shipment_review_page.dart`: Halaman review sebelum submit dengan validasi titik wajib yang kosong dan konfirmasi permanen (immutable).

## 3. Cara Menggunakan
```dart
Navigator.of(context).push(
  MaterialPageRoute(
    builder: (_) => ShipmentTimelinePage(
      shipmentId: 13,
      containerPlateNo: 'TGHU1234567',
      baseUrl: 'http://10.0.2.2:8000', // Gunakan IP server Laravel Anda
      sopPoints: pointsMasterData, // 27 Titik dari database
    ),
  ),
);
```
