import 'dart:io';

enum UploadStatus { idle, uploading, success, error }

class ShipmentPhotoItem {
  final int? pointNo;
  final String pointLabel;
  final bool isExtra;
  final bool isMandatory;
  final String? description;

  // State upload
  File? localFile;
  String? tempId;
  String? previewUrl;
  String? originalUrl;
  String? thumbnailUrl;
  String? stampedAt;
  String? capturedAt;
  String? ocrText;
  UploadStatus status;
  double uploadProgress; // 0.0 sampai 1.0
  String? errorMessage;

  ShipmentPhotoItem({
    this.pointNo,
    required this.pointLabel,
    this.isExtra = false,
    this.isMandatory = true,
    this.description,
    this.localFile,
    this.tempId,
    this.previewUrl,
    this.originalUrl,
    this.thumbnailUrl,
    this.stampedAt,
    this.capturedAt,
    this.ocrText,
    this.status = UploadStatus.idle,
    this.uploadProgress = 0.0,
    this.errorMessage,
  });

  bool get isUploaded => status == UploadStatus.success && tempId != null;

  Map<String, dynamic> toSubmitJson() {
    return {
      'temp_id': tempId,
      'point_no': pointNo,
      'point_label': pointLabel,
      'is_extra': isExtra,
      'ocr_text': ocrText,
    };
  }

  ShipmentPhotoItem copyWith({
    File? localFile,
    String? tempId,
    String? previewUrl,
    String? originalUrl,
    String? thumbnailUrl,
    String? stampedAt,
    String? capturedAt,
    String? ocrText,
    UploadStatus? status,
    double? uploadProgress,
    String? errorMessage,
  }) {
    return ShipmentPhotoItem(
      pointNo: pointNo,
      pointLabel: pointLabel,
      isExtra: isExtra,
      isMandatory: isMandatory,
      description: description,
      localFile: localFile ?? this.localFile,
      tempId: tempId ?? this.tempId,
      previewUrl: previewUrl ?? this.previewUrl,
      originalUrl: originalUrl ?? this.originalUrl,
      thumbnailUrl: thumbnailUrl ?? this.thumbnailUrl,
      stampedAt: stampedAt ?? this.stampedAt,
      capturedAt: capturedAt ?? this.capturedAt,
      ocrText: ocrText ?? this.ocrText,
      status: status ?? this.status,
      uploadProgress: uploadProgress ?? this.uploadProgress,
      errorMessage: errorMessage ?? this.errorMessage,
    );
  }
}
