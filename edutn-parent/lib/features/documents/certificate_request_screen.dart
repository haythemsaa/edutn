import 'package:flutter/material.dart';
import '../../core/api/api_client.dart';

class CertificateRequestScreen extends StatefulWidget {
  final int studentId;
  final String studentName;

  const CertificateRequestScreen({
    Key? key,
    required this.studentId,
    required this.studentName,
  }) : super(key: key);

  @override
  State<CertificateRequestScreen> createState() => _CertificateRequestScreenState();
}

class _CertificateRequestScreenState extends State<CertificateRequestScreen> {
  final ApiClient _apiClient = ApiClient();
  final _formKey = GlobalKey<FormState>();

  String? _selectedCertificateType;
  String? _notes;
  bool _isSubmitting = false;

  final List<Map<String, dynamic>> _certificateTypes = [
    {
      'value': 'scolarite',
      'label': 'Certificat de scolarité',
      'labelAr': 'شهادة مدرسية',
      'description': 'Atteste que l\'élève est inscrit dans l\'établissement',
      'icon': Icons.school,
    },
    {
      'value': 'inscription',
      'label': 'Certificat d\'inscription',
      'labelAr': 'شهادة تسجيل',
      'description': 'Confirme l\'inscription de l\'élève pour l\'année en cours',
      'icon': Icons.assignment,
    },
    {
      'value': 'notes',
      'label': 'Relevé de notes',
      'labelAr': 'كشف النقاط',
      'description': 'Document récapitulatif des notes obtenues',
      'icon': Icons.grade,
    },
    {
      'value': 'conduite',
      'label': 'Certificat de bonne conduite',
      'labelAr': 'شهادة حسن سيرة وسلوك',
      'description': 'Atteste du bon comportement de l\'élève',
      'icon': Icons.verified_user,
    },
    {
      'value': 'presence',
      'label': 'Certificat de présence',
      'labelAr': 'شهادة حضور',
      'description': 'Atteste de la régularité de l\'élève',
      'icon': Icons.event_available,
    },
    {
      'value': 'reussite',
      'label': 'Certificat de réussite',
      'labelAr': 'شهادة نجاح',
      'description': 'Confirme la réussite de l\'élève',
      'icon': Icons.emoji_events,
    },
  ];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Demander un certificat'),
      ),
      body: Form(
        key: _formKey,
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            // Student info card
            Card(
              color: Colors.blue[50],
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Row(
                  children: [
                    CircleAvatar(
                      backgroundColor: Colors.blue,
                      child: Text(
                        widget.studentName[0].toUpperCase(),
                        style: const TextStyle(color: Colors.white),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text(
                            'Demande pour',
                            style: TextStyle(fontSize: 12, color: Colors.grey),
                          ),
                          Text(
                            widget.studentName,
                            style: const TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 24),

            // Certificate type selection
            const Text(
              'Type de certificat',
              style: TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 12),
            const Text(
              'Sélectionnez le type de certificat dont vous avez besoin',
              style: TextStyle(fontSize: 14, color: Colors.grey),
            ),
            const SizedBox(height: 16),

            // Certificate type cards
            ..._certificateTypes.map((type) {
              return _buildCertificateTypeCard(type);
            }).toList(),

            const SizedBox(height: 24),

            // Notes field
            const Text(
              'Notes supplémentaires (optionnel)',
              style: TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 12),
            TextFormField(
              maxLines: 4,
              decoration: const InputDecoration(
                hintText: 'Ajoutez des informations supplémentaires si nécessaire...',
                border: OutlineInputBorder(),
              ),
              onSaved: (value) => _notes = value,
            ),

            const SizedBox(height: 24),

            // Info card
            Card(
              color: Colors.amber[50],
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Icon(Icons.info_outline, color: Colors.amber[700]),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'Délai de traitement',
                            style: TextStyle(
                              fontWeight: FontWeight.bold,
                              color: Colors.amber[900],
                            ),
                          ),
                          const SizedBox(height: 4),
                          const Text(
                            'Votre demande sera traitée dans un délai de 48 heures ouvrables. Vous recevrez une notification une fois le document prêt.',
                            style: TextStyle(fontSize: 14),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            ),

            const SizedBox(height: 32),

            // Submit button
            SizedBox(
              height: 50,
              child: ElevatedButton(
                onPressed: _isSubmitting ? null : _submitRequest,
                child: _isSubmitting
                    ? const SizedBox(
                        height: 20,
                        width: 20,
                        child: CircularProgressIndicator(
                          strokeWidth: 2,
                          valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                        ),
                      )
                    : const Text(
                        'Envoyer la demande',
                        style: TextStyle(fontSize: 16),
                      ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildCertificateTypeCard(Map<String, dynamic> type) {
    final isSelected = _selectedCertificateType == type['value'];

    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      elevation: isSelected ? 4 : 1,
      child: InkWell(
        onTap: () {
          setState(() {
            _selectedCertificateType = type['value'];
          });
        },
        child: Container(
          decoration: BoxDecoration(
            border: Border.all(
              color: isSelected ? Theme.of(context).primaryColor : Colors.transparent,
              width: 2,
            ),
            borderRadius: BorderRadius.circular(4),
          ),
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: isSelected
                        ? Theme.of(context).primaryColor.withOpacity(0.1)
                        : Colors.grey[200],
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Icon(
                    type['icon'],
                    color: isSelected ? Theme.of(context).primaryColor : Colors.grey[600],
                    size: 28,
                  ),
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        type['label'],
                        style: TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.bold,
                          color: isSelected ? Theme.of(context).primaryColor : Colors.black,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        type['labelAr'],
                        style: const TextStyle(
                          fontSize: 14,
                          color: Colors.grey,
                        ),
                        textDirection: TextDirection.rtl,
                      ),
                      const SizedBox(height: 8),
                      Text(
                        type['description'],
                        style: TextStyle(
                          fontSize: 13,
                          color: Colors.grey[600],
                        ),
                      ),
                    ],
                  ),
                ),
                if (isSelected)
                  Icon(
                    Icons.check_circle,
                    color: Theme.of(context).primaryColor,
                  ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Future<void> _submitRequest() async {
    if (!_formKey.currentState!.validate()) {
      return;
    }

    if (_selectedCertificateType == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Veuillez sélectionner un type de certificat'),
          backgroundColor: Colors.red,
        ),
      );
      return;
    }

    _formKey.currentState!.save();

    setState(() => _isSubmitting = true);

    try {
      await _apiClient.requestCertificate(
        widget.studentId,
        _selectedCertificateType!,
      );

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Demande envoyée avec succès'),
            backgroundColor: Colors.green,
          ),
        );
        Navigator.of(context).pop();
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Erreur: $e'),
            backgroundColor: Colors.red,
          ),
        );
      }
    } finally {
      if (mounted) {
        setState(() => _isSubmitting = false);
      }
    }
  }
}
