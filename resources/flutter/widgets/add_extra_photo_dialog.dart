import 'package:flutter/material.dart';

class AddExtraPhotoDialog extends StatefulWidget {
  final void Function(String label, bool isCamera) onConfirm;

  const AddExtraPhotoDialog({
    super.key,
    required this.onConfirm,
  });

  static Future<void> show(
    BuildContext context, {
    required void Function(String label, bool isCamera) onConfirm,
  }) {
    return showDialog(
      context: context,
      builder: (_) => AddExtraPhotoDialog(onConfirm: onConfirm),
    );
  }

  @override
  State<AddExtraPhotoDialog> createState() => _AddExtraPhotoDialogState();
}

class _AddExtraPhotoDialogState extends State<AddExtraPhotoDialog> {
  final TextEditingController _labelController = TextEditingController();
  final _formKey = GlobalKey<FormState>();

  final List<String> _quickSuggestions = [
    'Kerusakan Container Samping',
    'Segel Tambahan / Cadangan',
    'Kondisi Terpal Tambahan',
    'Pintu Belakang Pengunci',
    'Bale Ekstra Bermasalah',
  ];

  @override
  void dispose() {
    _labelController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return AlertDialog(
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
      title: const Row(
        children: [
          CircleAvatar(
            backgroundColor: Color(0xFFFAF5FF),
            radius: 16,
            child: Icon(Icons.add_a_photo, color: Colors.purple, size: 18),
          ),
          SizedBox(width: 10),
          Text('Tambah Foto Ekstra', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
        ],
      ),
      content: SingleChildScrollView(
        child: Form(
          key: _formKey,
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text(
                'Masukkan nama/keterangan bukti foto tambahan:',
                style: TextStyle(fontSize: 12, color: Colors.grey),
              ),
              const SizedBox(height: 10),
              TextFormField(
                controller: _labelController,
                autofocus: true,
                decoration: InputDecoration(
                  hintText: 'Contoh: Kerusakan Dinding Kiri',
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                  contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                ),
                validator: (val) {
                  if (val == null || val.trim().isEmpty) {
                    return 'Label foto tidak boleh kosong';
                  }
                  return null;
                },
              ),
              const SizedBox(height: 12),
              const Text(
                'Saran Cepat:',
                style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Colors.grey),
              ),
              const SizedBox(height: 6),
              Wrap(
                spacing: 6,
                runSpacing: 6,
                children: _quickSuggestions.map((s) {
                  return ActionChip(
                    label: Text(s, style: const TextStyle(fontSize: 10)),
                    onPressed: () {
                      setState(() {
                        _labelController.text = s;
                      });
                    },
                    backgroundColor: Colors.purple.shade50,
                    side: BorderSide(color: Colors.purple.shade200),
                  );
                }).toList(),
              ),
            ],
          ),
        ),
      ),
      actions: [
        TextButton(
          onPressed: () => Navigator.of(context).pop(),
          child: const Text('Batal'),
        ),
        ElevatedButton.icon(
          onPressed: () {
            if (_formKey.currentState!.validate()) {
              final label = _labelController.text.trim();
              Navigator.of(context).pop();
              widget.onConfirm(label, true); // Open camera
            }
          },
          icon: const Icon(Icons.camera_alt, size: 16),
          label: const Text('Buka Kamera'),
          style: ElevatedButton.styleFrom(
            backgroundColor: const Color(0xFF2563EB),
            foregroundColor: Colors.white,
          ),
        ),
      ],
    );
  }
}
