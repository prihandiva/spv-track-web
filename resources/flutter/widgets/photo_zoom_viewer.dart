import 'package:flutter/material.dart';
import '../models/shipment_photo_model.dart';

class PhotoZoomViewer extends StatefulWidget {
  final List<ShipmentPhotoItem> photos;
  final int initialIndex;

  const PhotoZoomViewer({
    super.key,
    required this.photos,
    this.initialIndex = 0,
  });

  static Future<void> show(
    BuildContext context, {
    required List<ShipmentPhotoItem> photos,
    int initialIndex = 0,
  }) {
    return Navigator.of(context).push(
      MaterialPageRoute(
        fullscreenDialog: true,
        builder: (_) => PhotoZoomViewer(
          photos: photos,
          initialIndex: initialIndex,
        ),
      ),
    );
  }

  @override
  State<PhotoZoomViewer> createState() => _PhotoZoomViewerState();
}

class _PhotoZoomViewerState extends State<PhotoZoomViewer> {
  late PageController _pageController;
  late int _currentIndex;
  bool _showOriginal = false;
  final TransformationController _transformController = TransformationController();
  TapDownDetails? _doubleTapDetails;

  @override
  void initState() {
    super.initState();
    _currentIndex = widget.initialIndex;
    _pageController = PageController(initialPage: widget.initialIndex);
  }

  @override
  void dispose() {
    _pageController.dispose();
    _transformController.dispose();
    super.dispose();
  }

  void _handleDoubleTap() {
    if (_transformController.value != Matrix4.identity()) {
      _transformController.value = Matrix4.identity();
    } else {
      final position = _doubleTapDetails?.localPosition ?? Offset.zero;
      _transformController.value = Matrix4.identity()
        ..translate(-position.dx * 1.5, -position.dy * 1.5)
        ..scale(2.5);
    }
  }

  @override
  Widget build(BuildContext context) {
    if (widget.photos.isEmpty) {
      return const Scaffold(
        backgroundColor: Colors.black,
        body: Center(child: Text('Tidak ada foto', style: TextStyle(color: Colors.white))),
      );
    }

    final currentPhoto = widget.photos[_currentIndex];

    return Scaffold(
      backgroundColor: Colors.black,
      body: Stack(
        children: [
          // 1. PageView foto dengan InteractiveViewer (Pinch & Double-tap zoom)
          PageView.builder(
            controller: _pageController,
            itemCount: widget.photos.length,
            onPageChanged: (idx) {
              setState(() {
                _currentIndex = idx;
                _showOriginal = false;
                _transformController.value = Matrix4.identity();
              });
            },
            itemBuilder: (context, index) {
              final photo = widget.photos[index];
              final imageUrl = _showOriginal
                  ? (photo.originalUrl ?? photo.previewUrl)
                  : (photo.previewUrl ?? photo.thumbnailUrl);

              return GestureDetector(
                onDoubleTapDown: (details) => _doubleTapDetails = details,
                onDoubleTap: _handleDoubleTap,
                child: Center(
                  child: InteractiveViewer(
                    transformationController: _transformController,
                    minScale: 1.0,
                    maxScale: 6.0,
                    child: photo.localFile != null && photo.previewUrl == null
                        ? Image.file(
                            photo.localFile!,
                            fit: BoxFit.contain,
                          )
                        : imageUrl != null
                            ? Image.network(
                                imageUrl,
                                fit: BoxFit.contain,
                                loadingBuilder: (context, child, progress) {
                                  if (progress == null) return child;
                                  final total = progress.expectedTotalBytes;
                                  final loaded = progress.cumulativeBytesLoaded;
                                  return Center(
                                    child: CircularProgressIndicator(
                                      value: total != null ? loaded / total : null,
                                      color: Colors.amber,
                                    ),
                                  );
                                },
                                errorBuilder: (_, __, ___) => const Center(
                                  child: Icon(Icons.broken_image, color: Colors.white54, size: 48),
                                ),
                              )
                            : const Center(
                                child: Text('Foto belum diunggah', style: TextStyle(color: Colors.white70)),
                              ),
                  ),
                ),
              );
            },
          ),

          // 2. Top App Bar Overlay
          Positioned(
            top: 0,
            left: 0,
            right: 0,
            child: Container(
              padding: EdgeInsets.only(
                top: MediaQuery.of(context).padding.top + 8,
                bottom: 12,
                left: 16,
                right: 16,
              ),
              decoration: const BoxDecoration(
                gradient: LinearGradient(
                  colors: [Colors.black87, Colors.transparent],
                  begin: Alignment.topCenter,
                  end: Alignment.bottomCenter,
                ),
              ),
              child: Row(
                children: [
                  IconButton(
                    icon: const Icon(Icons.close, color: Colors.white, size: 26),
                    onPressed: () => Navigator.of(context).pop(),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          currentPhoto.isExtra
                              ? 'Foto Ekstra: ${currentPhoto.pointLabel}'
                              : 'Titik ${currentPhoto.pointNo}: ${currentPhoto.pointLabel}',
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 14,
                            fontWeight: FontWeight.bold,
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                        Text(
                          '${_currentIndex + 1} dari ${widget.photos.length} foto',
                          style: const TextStyle(color: Colors.white70, fontSize: 11),
                        ),
                      ],
                    ),
                  ),
                  // Tombol ganti Stamped / Original
                  TextButton.icon(
                    onPressed: () {
                      setState(() {
                        _showOriginal = !_showOriginal;
                      });
                    },
                    icon: Icon(
                      _showOriginal ? Icons.verified : Icons.image,
                      size: 16,
                      color: _showOriginal ? Colors.amber : Colors.lightBlueAccent,
                    ),
                    label: Text(
                      _showOriginal ? 'Asli' : 'Stempel WIB',
                      style: TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.bold,
                        color: _showOriginal ? Colors.amber : Colors.lightBlueAccent,
                      ),
                    ),
                    style: TextButton.styleFrom(
                      backgroundColor: Colors.white12,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                    ),
                  ),
                ],
              ),
            ),
          ),

          // 3. Bottom Information HUD Overlay
          Positioned(
            bottom: 0,
            left: 0,
            right: 0,
            child: Container(
              padding: EdgeInsets.only(
                left: 16,
                right: 16,
                top: 16,
                bottom: MediaQuery.of(context).padding.bottom + 12,
              ),
              decoration: const BoxDecoration(
                gradient: LinearGradient(
                  colors: [Colors.transparent, Colors.black87, Colors.black],
                  begin: Alignment.topCenter,
                  end: Alignment.bottomCenter,
                ),
              ),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  if (currentPhoto.stampedAt != null)
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                      decoration: BoxDecoration(
                        color: Colors.amber.withOpacity(0.2),
                        border: Border.all(color: Colors.amber.withOpacity(0.5)),
                        borderRadius: BorderRadius.circular(6),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          const Icon(Icons.schedule, color: Colors.amber, size: 14),
                          const SizedBox(width: 6),
                          Text(
                            'Waktu Server: ${currentPhoto.stampedAt}',
                            style: const TextStyle(
                              color: Colors.amber,
                              fontSize: 12,
                              fontWeight: FontWeight.bold,
                              fontFamily: 'monospace',
                            ),
                          ),
                        ],
                      ),
                    ),
                  if (currentPhoto.description != null) ...[
                    const SizedBox(height: 6),
                    Text(
                      currentPhoto.description!,
                      style: const TextStyle(color: Colors.white70, fontSize: 11),
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ],
                  const SizedBox(height: 8),
                  const Text(
                    'Petunjuk: Cubit (pinch) atau ketuk dua kali untuk memperbesar nomor container / segel.',
                    style: TextStyle(color: Colors.white38, fontSize: 10, fontStyle: FontStyle.italic),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}
