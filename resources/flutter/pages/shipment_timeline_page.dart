import 'dart:io';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import '../models/shipment_photo_model.dart';
import '../services/shipment_photo_api_service.dart';
import '../widgets/add_extra_photo_dialog.dart';
import '../widgets/photo_zoom_viewer.dart';
import '../widgets/timeline_photo_card.dart';
import 'shipment_review_page.dart';

class ShipmentTimelinePage extends StatefulWidget {
  final int shipmentId;
  final String containerPlateNo;
  final String baseUrl;
  final String? authToken;
  final List<Map<String, dynamic>> sopPoints; // Master 27 SOP points

  const ShipmentTimelinePage({
    super.key,
    required this.shipmentId,
    required this.containerPlateNo,
    required this.baseUrl,
    this.authToken,
    required this.sopPoints,
  });

  @override
  State<ShipmentTimelinePage> createState() => _ShipmentTimelinePageState();
}

class _ShipmentTimelinePageState extends State<ShipmentTimelinePage> {
  late final ShipmentPhotoApiService _apiService;
  final ImagePicker _picker = ImagePicker();
  final List<ShipmentPhotoItem> _photos = [];

  String _serverTimeWib = 'Memuat jam server...';
  bool _isInit = true;

  @override
  void initState() {
    super.initState();
    _apiService = ShipmentPhotoApiService(
      baseUrl: widget.baseUrl,
      authToken: widget.authToken,
    );
    _initializePoints();
    _fetchServerClock();
  }

  void _initializePoints() {
    for (final point in widget.sopPoints) {
      _photos.add(
        ShipmentPhotoItem(
          pointNo: point['urutan'] as int?,
          pointLabel: point['nama_titik'] as String? ?? 'Titik SOP',
          isMandatory: point['wajib'] == true,
          description: point['deskripsi'] as String?,
          isExtra: false,
        ),
      );
    }
    setState(() => _isInit = false);
  }

  Future<void> _fetchServerClock() async {
    try {
      final timeStr = await _apiService.fetchServerTime();
      if (mounted && timeStr.isNotEmpty) {
        setState(() => _serverTimeWib = timeStr);
      }
    } catch (_) {}
  }

  /// Memilih dan mengunggah foto ke endpoint sementara secara non-blocking
  Future<void> _pickAndUploadPhoto(int index, ImageSource source) async {
    try {
      final picked = await _picker.pickImage(
        source: source,
        imageQuality: 92,
        maxWidth: 2400,
        maxHeight: 2400,
      );

      if (picked == null) return;

      final file = File(picked.path);
      final item = _photos[index];

      setState(() {
        item.localFile = file;
        item.status = UploadStatus.uploading;
        item.uploadProgress = 0.05;
        item.errorMessage = null;
      });

      final capturedAt = DateTime.now();

      // Panggil upload sementara
      final res = await _apiService.uploadPhotoTemp(
        file: file,
        pointNo: item.pointNo,
        pointLabel: item.pointLabel,
        isExtra: item.isExtra,
        capturedAt: capturedAt,
        onProgress: (progress) {
          if (mounted) {
            setState(() {
              item.uploadProgress = progress;
            });
          }
        },
      );

      if (mounted) {
        setState(() {
          item.tempId = res['temp_id'] as String?;
          item.previewUrl = res['preview_url'] as String?;
          item.originalUrl = res['original_url'] as String?;
          item.thumbnailUrl = res['thumbnail_url'] as String?;
          item.stampedAt = res['stamped_at'] as String?;
          item.capturedAt = res['captured_at'] as String?;
          item.status = UploadStatus.success;
          item.uploadProgress = 1.0;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _photos[index].status = UploadStatus.error;
          _photos[index].errorMessage = e.toString().replaceAll('Exception: ', '');
        });
      }
    }
  }

  /// Menambah foto ekstra dari tombol (+)
  void _addNewExtraPhoto(String label, bool isCamera) {
    final newExtra = ShipmentPhotoItem(
      pointNo: null,
      pointLabel: label,
      isExtra: true,
      isMandatory: false,
    );

    setState(() {
      _photos.add(newExtra);
    });

    final newIndex = _photos.length - 1;
    _pickAndUploadPhoto(newIndex, isCamera ? ImageSource.camera : ImageSource.gallery);
  }

  int get _uploadedCount => _photos.where((p) => p.isUploaded).length;

  @override
  Widget build(BuildContext context) {
    if (_isInit) {
      return const Scaffold(body: Center(child: CircularProgressIndicator()));
    }

    return Scaffold(
      appBar: AppBar(
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Evidence: ${widget.containerPlateNo}',
              style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
            ),
            Row(
              children: [
                const Icon(Icons.access_time_filled, size: 12, color: Colors.amber),
                const SizedBox(width: 4),
                Text(
                  _serverTimeWib,
                  style: const TextStyle(fontSize: 11, color: Colors.amber, fontFamily: 'monospace'),
                ),
              ],
            ),
          ],
        ),
        backgroundColor: const Color(0xFF1E3A8A),
        foregroundColor: Colors.white,
        actions: [
          IconButton(
            tooltip: 'Review Sebelum Submit',
            icon: const Icon(Icons.fact_check_outlined),
            onPressed: () {
              Navigator.of(context).push(
                MaterialPageRoute(
                  builder: (_) => ShipmentReviewPage(
                    shipmentId: widget.shipmentId,
                    containerPlateNo: widget.containerPlateNo,
                    allPhotos: _photos,
                    apiService: _apiService,
                    onSubmitSuccess: () => Navigator.of(context).pop(),
                  ),
                ),
              );
            },
          ),
        ],
      ),
      body: ListView.builder(
        padding: const EdgeInsets.only(top: 8, bottom: 90),
        itemCount: _photos.length,
        itemBuilder: (context, index) {
          final photo = _photos[index];
          return TimelinePhotoCard(
            photo: photo,
            onPickCamera: () => _pickAndUploadPhoto(index, ImageSource.camera),
            onPickGallery: () => _pickAndUploadPhoto(index, ImageSource.gallery),
            onRetake: () => _pickAndUploadPhoto(index, ImageSource.camera),
            onRetry: () => _pickAndUploadPhoto(index, ImageSource.camera),
            onZoomTap: () {
              // Buka PhotoZoomViewer
              final uploadedList = _photos.where((p) => p.isUploaded).toList();
              final targetIndex = uploadedList.indexOf(photo);
              PhotoZoomViewer.show(
                context,
                photos: uploadedList,
                initialIndex: targetIndex >= 0 ? targetIndex : 0,
              );
            },
          );
        },
      ),

      // Tombol (+) Foto Ekstra
      floatingActionButton: Padding(
        padding: const EdgeInsets.only(bottom: 60),
        child: FloatingActionButton.extended(
          onPressed: () {
            AddExtraPhotoDialog.show(
              context,
              onConfirm: _addNewExtraPhoto,
            );
          },
          backgroundColor: const Color(0xFF7C3AED),
          foregroundColor: Colors.white,
          icon: const Icon(Icons.add_a_photo),
          label: const Text('Foto Ekstra (+)'),
        ),
      ),

      // Bottom Bar Menuju Halaman Review
      bottomSheet: Container(
        height: 65,
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
        decoration: BoxDecoration(
          color: Colors.white,
          boxShadow: [
            BoxShadow(color: Colors.black.withOpacity(0.08), blurRadius: 10, offset: const Offset(0, -3)),
          ],
        ),
        child: Row(
          children: [
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Text(
                  '$_uploadedCount / ${_photos.length} Terunggah',
                  style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold),
                ),
                Text(
                  '27 Titik SOP + ${_photos.where((p) => p.isExtra).length} Ekstra',
                  style: TextStyle(fontSize: 11, color: Colors.grey.shade600),
                ),
              ],
            ),
            const Spacer(),
            ElevatedButton.icon(
              onPressed: () {
                Navigator.of(context).push(
                  MaterialPageRoute(
                    builder: (_) => ShipmentReviewPage(
                      shipmentId: widget.shipmentId,
                      containerPlateNo: widget.containerPlateNo,
                      allPhotos: _photos,
                      apiService: _apiService,
                      onSubmitSuccess: () => Navigator.of(context).pop(),
                    ),
                  ),
                );
              },
              icon: const Icon(Icons.arrow_forward, size: 16),
              label: const Text('Review & Submit'),
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFF2563EB),
                foregroundColor: Colors.white,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
