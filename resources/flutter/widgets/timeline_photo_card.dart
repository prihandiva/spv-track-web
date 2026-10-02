import 'package:flutter/material.dart';
import '../models/shipment_photo_model.dart';

class TimelinePhotoCard extends StatelessWidget {
  final ShipmentPhotoItem photo;
  final VoidCallback onPickCamera;
  final VoidCallback onPickGallery;
  final VoidCallback onRetake;
  final VoidCallback onZoomTap;
  final VoidCallback onRetry;

  const TimelinePhotoCard({
    super.key,
    required this.photo,
    required this.onPickCamera,
    required this.onPickGallery,
    required this.onRetake,
    required this.onZoomTap,
    required this.onRetry,
  });

  @override
  Widget build(BuildContext context) {
    final isUploaded = photo.isUploaded;
    final isUploading = photo.status == UploadStatus.uploading;
    final isError = photo.status == UploadStatus.error;

    return Card(
      elevation: 2,
      margin: const EdgeInsets.symmetric(horizontal: 14, vertical: 7),
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(16),
        side: BorderSide(
          color: isError
              ? Colors.red.shade300
              : isUploaded
                  ? Colors.green.shade200
                  : Colors.grey.shade200,
          width: isUploaded || isError ? 1.5 : 1,
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // 1. Header Card: Nomor titik, Label & Badges
          Padding(
            padding: const EdgeInsets.all(12),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                CircleAvatar(
                  radius: 14,
                  backgroundColor: photo.isExtra
                      ? Colors.purple.shade50
                      : isUploaded
                          ? Colors.green.shade50
                          : Colors.blue.shade50,
                  child: Text(
                    photo.isExtra ? '+' : '${photo.pointNo ?? ""}',
                    style: TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.bold,
                      color: photo.isExtra
                          ? Colors.purple
                          : isUploaded
                              ? Colors.green.shade800
                              : Colors.blue.shade800,
                    ),
                  ),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Expanded(
                            child: Text(
                              photo.pointLabel,
                              style: const TextStyle(
                                fontSize: 13,
                                fontWeight: FontWeight.bold,
                                color: Color(0xFF1E293B),
                              ),
                            ),
                          ),
                          if (photo.isMandatory && !photo.isExtra)
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                              decoration: BoxDecoration(
                                color: Colors.red.shade50,
                                borderRadius: BorderRadius.circular(4),
                              ),
                              child: Text(
                                'Wajib',
                                style: TextStyle(
                                  color: Colors.red.shade700,
                                  fontSize: 9,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                            ),
                          if (photo.isExtra)
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                              decoration: BoxDecoration(
                                color: Colors.purple.shade50,
                                borderRadius: BorderRadius.circular(4),
                              ),
                              child: Text(
                                'Ekstra',
                                style: TextStyle(
                                  color: Colors.purple.shade700,
                                  fontSize: 9,
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                            ),
                        ],
                      ),
                      if (photo.description != null && photo.description!.isNotEmpty)
                        Padding(
                          padding: const EdgeInsets.only(top: 2),
                          child: Text(
                            photo.description!,
                            style: TextStyle(fontSize: 11, color: Colors.grey.shade600),
                            maxLines: 2,
                            overflow: TextOverflow.ellipsis,
                          ),
                        ),
                    ],
                  ),
                ),
              ],
            ),
          ),

          // 2. Area Gambar / Placeholder Upload
          Container(
            height: 180,
            width: double.infinity,
            color: const Color(0xFF0F172A),
            child: Stack(
              fit: StackFit.expand,
              children: [
                if (isUploaded && photo.thumbnailUrl != null) ...[
                  // Thumbnail gambar dengan gesture zoom tap
                  GestureDetector(
                    onTap: onZoomTap,
                    child: Image.network(
                      photo.thumbnailUrl!,
                      fit: BoxFit.cover,
                      loadingBuilder: (context, child, loadingProgress) {
                        if (loadingProgress == null) return child;
                        return const Center(child: CircularProgressIndicator(strokeWidth: 2));
                      },
                      errorBuilder: (_, __, ___) => const Center(
                        child: Icon(Icons.broken_image, color: Colors.white54, size: 36),
                      ),
                    ),
                  ),

                  // Overlay gradient gelap di bawah untuk readability timestamp
                  Positioned(
                    bottom: 0,
                    left: 0,
                    right: 0,
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                      decoration: const BoxDecoration(
                        gradient: LinearGradient(
                          colors: [Colors.transparent, Colors.black87],
                          begin: Alignment.topCenter,
                          end: Alignment.bottomCenter,
                        ),
                      ),
                      child: Row(
                        children: [
                          const Icon(Icons.verified, color: Colors.amber, size: 14),
                          const SizedBox(width: 4),
                          Expanded(
                            child: Text(
                              photo.stampedAt ?? 'WIB Terstempel',
                              style: const TextStyle(
                                color: Colors.amber,
                                fontSize: 10,
                                fontWeight: FontWeight.bold,
                                fontFamily: 'monospace',
                              ),
                            ),
                          ),
                          InkWell(
                            onTap: onZoomTap,
                            child: Container(
                              padding: const EdgeInsets.all(4),
                              decoration: BoxDecoration(
                                color: Colors.black54,
                                borderRadius: BorderRadius.circular(4),
                              ),
                              child: const Row(
                                children: [
                                  Icon(Icons.zoom_in, color: Colors.white, size: 14),
                                  SizedBox(width: 2),
                                  Text(
                                    'Zoom',
                                    style: TextStyle(color: Colors.white, fontSize: 10),
                                  ),
                                ],
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ] else if (isUploading) ...[
                  // State Mengunggah (Non-blocking progress)
                  Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        CircularProgressIndicator(
                          value: photo.uploadProgress > 0 ? photo.uploadProgress : null,
                          color: Colors.amber,
                          strokeWidth: 3,
                        ),
                        const SizedBox(height: 10),
                        Text(
                          'Menempelkan jam server... ${(photo.uploadProgress * 100).toInt()}%',
                          style: const TextStyle(color: Colors.white, fontSize: 12),
                        ),
                      ],
                    ),
                  ),
                ] else if (isError) ...[
                  // State Error / Gagal Upload
                  Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        const Icon(Icons.error_outline, color: Colors.redAccent, size: 36),
                        const SizedBox(height: 6),
                        Padding(
                          padding: const EdgeInsets.symmetric(horizontal: 16),
                          child: Text(
                            photo.errorMessage ?? 'Gagal mengunggah foto',
                            textAlign: TextAlign.center,
                            style: const TextStyle(color: Colors.white70, fontSize: 11),
                          ),
                        ),
                        const SizedBox(height: 8),
                        ElevatedButton.icon(
                          onPressed: onRetry,
                          icon: const Icon(Icons.refresh, size: 14),
                          label: const Text('Coba Lagi', style: TextStyle(fontSize: 11)),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: Colors.redAccent,
                            foregroundColor: Colors.white,
                            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                          ),
                        ),
                      ],
                    ),
                  ),
                ] else ...[
                  // State Kosong / Belum diunggah
                  Center(
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(Icons.camera_alt_outlined, size: 38, color: Colors.grey.shade600),
                        const SizedBox(height: 8),
                        Text(
                          'Foto belum diambil',
                          style: TextStyle(color: Colors.grey.shade400, fontSize: 12),
                        ),
                        const SizedBox(height: 10),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            ElevatedButton.icon(
                              onPressed: onPickCamera,
                              icon: const Icon(Icons.camera_alt, size: 14),
                              label: const Text('Kamera', style: TextStyle(fontSize: 11)),
                              style: ElevatedButton.styleFrom(
                                backgroundColor: const Color(0xFF2563EB),
                                foregroundColor: Colors.white,
                                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                              ),
                            ),
                            const SizedBox(width: 8),
                            OutlinedButton.icon(
                              onPressed: onPickGallery,
                              icon: const Icon(Icons.photo_library, size: 14),
                              label: const Text('Galeri', style: TextStyle(fontSize: 11)),
                              style: OutlinedButton.styleFrom(
                                foregroundColor: Colors.white,
                                side: const BorderSide(color: Colors.white38),
                                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                ],
              ],
            ),
          ),

          // 3. Footer Card: Status & Tombol Aksi
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
            child: Row(
              children: [
                Icon(
                  isUploaded
                      ? Icons.check_circle
                      : isError
                          ? Icons.warning_amber
                          : Icons.pending_outlined,
                  size: 14,
                  color: isUploaded
                      ? Colors.green
                      : isError
                          ? Colors.red
                          : Colors.grey,
                ),
                const SizedBox(width: 6),
                Text(
                  isUploaded
                      ? 'Terverifikasi server'
                      : isError
                          ? 'Perlu upload ulang'
                          : 'Belum diunggah',
                  style: TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.w600,
                    color: isUploaded
                        ? Colors.green.shade800
                        : isError
                            ? Colors.red.shade700
                            : Colors.grey.shade600,
                  ),
                ),
                const Spacer(),
                if (isUploaded)
                  TextButton.icon(
                    onPressed: onRetake,
                    icon: const Icon(Icons.replay, size: 14),
                    label: const Text('Ambil Ulang', style: TextStyle(fontSize: 11)),
                    style: TextButton.styleFrom(
                      foregroundColor: const Color(0xFF2563EB),
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                    ),
                  ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
