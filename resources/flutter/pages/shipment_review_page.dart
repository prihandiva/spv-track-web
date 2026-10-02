import 'package:flutter/material.dart';
import '../models/shipment_photo_model.dart';
import '../services/shipment_photo_api_service.dart';
import '../widgets/photo_zoom_viewer.dart';

class ShipmentReviewPage extends StatefulWidget {
  final int shipmentId;
  final String containerPlateNo;
  final List<ShipmentPhotoItem> allPhotos;
  final ShipmentPhotoApiService apiService;
  final VoidCallback onSubmitSuccess;

  const ShipmentReviewPage({
    super.key,
    required this.shipmentId,
    required this.containerPlateNo,
    required this.allPhotos,
    required this.apiService,
    required this.onSubmitSuccess,
  });

  @override
  State<ShipmentReviewPage> createState() => _ShipmentReviewPageState();
}

class _ShipmentReviewPageState extends State<ShipmentReviewPage> {
  bool _isSubmitting = false;

  List<ShipmentPhotoItem> get _uploadedPhotos =>
      widget.allPhotos.where((p) => p.isUploaded).toList();

  List<ShipmentPhotoItem> get _missingMandatoryPhotos => widget.allPhotos
      .where((p) => p.isMandatory && !p.isExtra && !p.isUploaded)
      .toList();

  int get _extraPhotoCount =>
      widget.allPhotos.where((p) => p.isExtra && p.isUploaded).count();

  Future<void> _handleFinalSubmit() async {
    final missing = _missingMandatoryPhotos;

    if (missing.isNotEmpty) {
      final confirm = await showDialog<bool>(
        context: context,
        builder: (ctx) => AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
          title: const Row(
            children: [
              Icon(Icons.warning_amber_rounded, color: Colors.orange, size: 28),
              SizedBox(width: 8),
              Text('Peringatan Titik Wajib'),
            ],
          ),
          content: Text(
            'Ada ${missing.length} titik SOP wajib yang belum diunggah:\n' +
                missing.map((m) => '• Titik ${m.pointNo}: ${m.pointLabel}').join('\n') +
                '\n\nApakah Anda yakin ingin tetap melakukan submit final?',
            style: const TextStyle(fontSize: 13),
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.of(ctx).pop(false),
              child: const Text('Lengkapi Dulu'),
            ),
            ElevatedButton(
              onPressed: () => Navigator.of(ctx).pop(true),
              style: ElevatedButton.styleFrom(backgroundColor: Colors.orange),
              child: const Text('Tetap Submit'),
            ),
          ],
        ),
      );

      if (confirm != true) return;
    } else {
      final confirm = await showDialog<bool>(
        context: context,
        builder: (ctx) => AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
          title: const Text('Konfirmasi Submit Final'),
          content: const Text(
            'Perhatian:\nFoto yang sudah tersimpan permanen bersifat IMMUTABLE (tidak dapat diubah atau dihapus lagi).\n\nPastikan nomor container, segel, dan bales sudah terbaca jelas melalui fitur Zoom.',
            style: TextStyle(fontSize: 13),
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.of(ctx).pop(false),
              child: const Text('Periksa Kembali'),
            ),
            ElevatedButton(
              onPressed: () => Navigator.of(ctx).pop(true),
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFF16A34A),
                foregroundColor: Colors.white,
              ),
              child: const Text('Ya, Submit Permanen'),
            ),
          ],
        ),
      );

      if (confirm != true) return;
    }

    setState(() => _isSubmitting = true);

    try {
      await widget.apiService.submitShipmentPhotos(
        shipmentId: widget.shipmentId,
        photos: widget.allPhotos,
      );

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Berhasil! Seluruh foto evidence telah disimpan permanen.'),
            backgroundColor: Color(0xFF16A34A),
          ),
        );
        widget.onSubmitSuccess();
        Navigator.of(context).pop();
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Gagal submit: $e'),
            backgroundColor: Colors.redAccent,
          ),
        );
      }
    } finally {
      if (mounted) setState(() => _isSubmitting = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final uploaded = _uploadedPhotos;
    final missing = _missingMandatoryPhotos;
    final totalMandatory = widget.allPhotos.where((p) => p.isMandatory && !p.isExtra).length;
    final completedMandatory = totalMandatory - missing.length;

    return Scaffold(
      appBar: AppBar(
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text('Review Evidence Sebelum Submit', style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold)),
            Text(widget.containerPlateNo, style: const TextStyle(fontSize: 12, color: Colors.white70)),
          ],
        ),
        backgroundColor: const Color(0xFF1E3A8A),
        foregroundColor: Colors.white,
      ),
      body: Column(
        children: [
          // 1. Ringkasan Statistik
          Container(
            padding: const EdgeInsets.all(14),
            color: Colors.white,
            child: Row(
              children: [
                Expanded(
                  child: _buildSummaryCard(
                    title: 'Titik SOP Wajib',
                    value: '$completedMandatory / $totalMandatory',
                    color: missing.isEmpty ? Colors.green : Colors.orange,
                    icon: missing.isEmpty ? Icons.check_circle : Icons.warning_amber,
                  ),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: _buildSummaryCard(
                    title: 'Total Foto Siap',
                    value: '${uploaded.length} Foto',
                    subtitle: '+${widget.allPhotos.where((p) => p.isExtra && p.isUploaded).length} Ekstra',
                    color: const Color(0xFF2563EB),
                    icon: Icons.photo_library,
                  ),
                ),
              ],
            ),
          ),

          // 2. Banner Peringatan Jika Ada Titik Wajib Kosong
          if (missing.isNotEmpty)
            Container(
              margin: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: Colors.red.shade50,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: Colors.red.shade200),
              ),
              child: Row(
                children: [
                  const Icon(Icons.error_outline, color: Colors.red, size: 24),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          '${missing.length} Titik Wajib Belum Diunggah!',
                          style: TextStyle(
                            fontSize: 13,
                            fontWeight: FontWeight.bold,
                            color: Colors.red.shade900,
                          ),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          'Periksa kembali sebelum melanjutkan: ${missing.map((m) => m.pointNo).join(', ')}',
                          style: TextStyle(fontSize: 11, color: Colors.red.shade700),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),

          // 3. Grid Preview Seluruh Foto
          Expanded(
            child: GridView.builder(
              padding: const EdgeInsets.all(14),
              gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                crossAxisCount: 2,
                crossAxisSpacing: 10,
                mainAxisSpacing: 10,
                childAspectRatio: 0.85,
              ),
              itemCount: widget.allPhotos.length,
              itemBuilder: (context, index) {
                final photo = widget.allPhotos[index];
                return _buildPhotoGridItem(photo);
              },
            ),
          ),

          // 4. Bottom Action Bar: Tombol Submit Final
          Container(
            padding: EdgeInsets.only(
              left: 16,
              right: 16,
              top: 12,
              bottom: MediaQuery.of(context).padding.bottom + 12,
            ),
            decoration: BoxDecoration(
              color: Colors.white,
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withOpacity(0.08),
                  blurRadius: 10,
                  offset: const Offset(0, -3),
                ),
              ],
            ),
            child: SizedBox(
              width: double.infinity,
              height: 50,
              child: ElevatedButton.icon(
                onPressed: _isSubmitting ? null : _handleFinalSubmit,
                icon: _isSubmitting
                    ? const SizedBox(
                        width: 20,
                        height: 20,
                        child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                      )
                    : const Icon(Icons.check_circle_outline, size: 20),
                label: Text(
                  _isSubmitting
                      ? 'Menyimpan Bukti ke Server...'
                      : 'Submit Final Evidence (${uploaded.length} Foto)',
                  style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold),
                ),
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF16A34A),
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                  elevation: 2,
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSummaryCard({
    required String title,
    required String value,
    String? subtitle,
    required Color color,
    required IconData icon,
  }) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: color.withOpacity(0.08),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: color.withOpacity(0.2)),
      ),
      child: Row(
        children: [
          CircleAvatar(
            backgroundColor: color.withOpacity(0.15),
            radius: 18,
            child: Icon(icon, color: color, size: 20),
          ),
          const SizedBox(width: 10),
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(title, style: TextStyle(fontSize: 11, color: Colors.grey.shade700)),
              Text(
                value,
                style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: color),
              ),
              if (subtitle != null)
                Text(subtitle, style: TextStyle(fontSize: 10, color: Colors.grey.shade500)),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildPhotoGridItem(ShipmentPhotoItem photo) {
    final isUploaded = photo.isUploaded;

    return InkWell(
      onTap: isUploaded
          ? () {
              // Buka PhotoZoomViewer
              final uploadedIndex = _uploadedPhotos.indexOf(photo);
              PhotoZoomViewer.show(
                context,
                photos: _uploadedPhotos,
                initialIndex: uploadedIndex >= 0 ? uploadedIndex : 0,
              );
            }
          : null,
      borderRadius: BorderRadius.circular(12),
      child: Container(
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(12),
          border: Border.all(
            color: isUploaded ? Colors.grey.shade300 : Colors.red.shade200,
            width: isUploaded ? 1 : 1.5,
          ),
          color: isUploaded ? Colors.white : Colors.red.shade50.withOpacity(0.4),
        ),
        clipBehavior: Clip.antiAlias,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Gambar thumbnail atau placeholder
            Expanded(
              child: Stack(
                fit: StackFit.expand,
                children: [
                  if (isUploaded && photo.thumbnailUrl != null) ...[
                    Image.network(
                      photo.thumbnailUrl!,
                      fit: BoxFit.cover,
                      errorBuilder: (_, __, ___) => const Center(
                        child: Icon(Icons.broken_image, color: Colors.grey),
                      ),
                    ),
                    Positioned(
                      bottom: 4,
                      right: 4,
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                        decoration: BoxDecoration(
                          color: Colors.black.withOpacity(0.7),
                          borderRadius: BorderRadius.circular(4),
                        ),
                        child: const Row(
                          children: [
                            Icon(Icons.zoom_in, color: Colors.white, size: 12),
                            SizedBox(width: 2),
                            Text('Zoom', style: TextStyle(color: Colors.white, fontSize: 9)),
                          ],
                        ),
                      ),
                    ),
                  ] else ...[
                    Center(
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(
                            Icons.error_outline,
                            color: photo.isMandatory ? Colors.red : Colors.grey,
                            size: 32,
                          ),
                          const SizedBox(height: 4),
                          Text(
                            photo.isMandatory ? 'Wajib Diisi' : 'Belum Diunggah',
                            style: TextStyle(
                              fontSize: 10,
                              fontWeight: FontWeight.bold,
                              color: photo.isMandatory ? Colors.red : Colors.grey,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ],
              ),
            ),

            // Keterangan di bawah thumbnail
            Padding(
              padding: const EdgeInsets.all(8),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    photo.isExtra ? '[+] ${photo.pointLabel}' : '${photo.pointNo}. ${photo.pointLabel}',
                    style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                  const SizedBox(height: 2),
                  Text(
                    isUploaded
                        ? (photo.stampedAt ?? 'Tersimpan')
                        : (photo.isMandatory ? 'Titik Wajib Kosong' : 'Opsional'),
                    style: TextStyle(
                      fontSize: 9,
                      color: isUploaded
                          ? Colors.green.shade800
                          : (photo.isMandatory ? Colors.red.shade700 : Colors.grey),
                      fontWeight: isUploaded ? FontWeight.w600 : FontWeight.normal,
                    ),
                    maxLines: 1,
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}
