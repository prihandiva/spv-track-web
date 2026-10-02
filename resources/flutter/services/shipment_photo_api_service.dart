import 'dart:convert';
import 'dart:io';
import 'package:http/http.dart' as http;
import '../models/shipment_photo_model.dart';

class ShipmentPhotoApiService {
  final String baseUrl;
  final String? authToken;

  ShipmentPhotoApiService({
    required this.baseUrl,
    this.authToken,
  });

  Map<String, String> get _headers => {
        'Accept': 'application/json',
        if (authToken != null) 'Authorization': 'Bearer $authToken',
      };

  /// Upload foto ke server sementara (Tahap 1) dengan callback progress
  Future<Map<String, dynamic>> uploadPhotoTemp({
    required File file,
    int? pointNo,
    required String pointLabel,
    bool isExtra = false,
    DateTime? capturedAt,
    void Function(double progress)? onProgress,
  }) async {
    final uri = Uri.parse('$baseUrl/api/photos/temp');
    final request = http.MultipartRequest('POST', uri);

    request.headers.addAll(_headers);

    if (pointNo != null) {
      request.fields['point_no'] = pointNo.toString();
    }
    request.fields['point_label'] = pointLabel;
    request.fields['is_extra'] = isExtra ? '1' : '0';
    if (capturedAt != null) {
      request.fields['captured_at'] = capturedAt.toIso8601String();
    }

    final multipartFile = await http.MultipartFile.fromPath('file', file.path);
    request.files.add(multipartFile);

    // Kirim request dan pantau progress
    final streamedResponse = await request.send();
    final totalBytes = streamedResponse.contentLength ?? 0;
    int bytesReceived = 0;

    final responseBodyBytes = <int>[];
    await for (final chunk in streamedResponse.stream) {
      responseBodyBytes.addAll(chunk);
      bytesReceived += chunk.length;
      if (totalBytes > 0 && onProgress != null) {
        onProgress(bytesReceived / totalBytes);
      }
    }

    final responseString = utf8.decode(responseBodyBytes);
    final json = jsonDecode(responseString);

    if (streamedResponse.statusCode == 201 && json['success'] == true) {
      return json['data'] as Map<String, dynamic>;
    } else {
      throw Exception(json['message'] ?? 'Gagal mengunggah foto sementara');
    }
  }

  /// Submit final semua foto evidence shipment ke penyimpanan permanen (Tahap 2)
  Future<Map<String, dynamic>> submitShipmentPhotos({
    required int shipmentId,
    required List<ShipmentPhotoItem> photos,
    Map<String, dynamic>? additionalShipmentData,
  }) async {
    final uri = Uri.parse('$baseUrl/api/shipments/$shipmentId/photos/submit');

    final payload = {
      'photos': photos.where((p) => p.isUploaded).map((p) => p.toSubmitJson()).toList(),
      if (additionalShipmentData != null) ...additionalShipmentData,
    };

    final response = await http.post(
      uri,
      headers: {
        ..._headers,
        'Content-Type': 'application/json',
      },
      body: jsonEncode(payload),
    );

    final json = jsonDecode(response.body);

    if (response.statusCode == 200 && json['success'] == true) {
      return json['data'] as Map<String, dynamic>;
    } else {
      throw Exception(json['message'] ?? 'Gagal submit foto evidence');
    }
  }

  /// Mengambil waktu server WIB
  Future<String> fetchServerTime() async {
    final uri = Uri.parse('$baseUrl/api/server-time');
    final response = await http.get(uri, headers: _headers);

    if (response.statusCode == 200) {
      final json = jsonDecode(response.body);
      return json['formatted'] ?? json['time'] ?? 'WIB';
    }
    return '';
  }
}
