import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../../core/api/api_client.dart';
import 'document_model.dart';
import 'document_viewer_screen.dart';
import 'certificate_request_screen.dart';

class DocumentsScreen extends StatefulWidget {
  final int studentId;
  final String studentName;

  const DocumentsScreen({
    Key? key,
    required this.studentId,
    required this.studentName,
  }) : super(key: key);

  @override
  State<DocumentsScreen> createState() => _DocumentsScreenState();
}

class _DocumentsScreenState extends State<DocumentsScreen> {
  final ApiClient _apiClient = ApiClient();
  List<Document> _documents = [];
  bool _isLoading = true;
  String? _error;
  String _filterType = 'all';

  final List<Map<String, String>> _documentTypes = [
    {'value': 'all', 'label': 'Tous', 'labelAr': 'الكل'},
    {'value': 'certificate', 'label': 'Certificats', 'labelAr': 'شهادات'},
    {'value': 'report', 'label': 'Bulletins', 'labelAr': 'كشوف النقاط'},
    {'value': 'authorization', 'label': 'Autorisations', 'labelAr': 'تراخيص'},
    {'value': 'medical', 'label': 'Médicaux', 'labelAr': 'طبية'},
    {'value': 'insurance', 'label': 'Assurance', 'labelAr': 'تأمين'},
    {'value': 'other', 'label': 'Autres', 'labelAr': 'أخرى'},
  ];

  @override
  void initState() {
    super.initState();
    _loadDocuments();
  }

  Future<void> _loadDocuments() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      final data = await _apiClient.getDocuments(widget.studentId);
      setState(() {
        _documents = data.map((json) => Document.fromJson(json)).toList();
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _error = e.toString();
        _isLoading = false;
      });
    }
  }

  List<Document> get _filteredDocuments {
    if (_filterType == 'all') return _documents;
    return _documents.where((doc) => doc.documentType == _filterType).toList();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text('Documents'),
            Text(
              widget.studentName,
              style: const TextStyle(fontSize: 14, fontWeight: FontWeight.normal),
            ),
          ],
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.add_circle_outline),
            onPressed: () {
              Navigator.push(
                context,
                MaterialPageRoute(
                  builder: (context) => CertificateRequestScreen(
                    studentId: widget.studentId,
                    studentName: widget.studentName,
                  ),
                ),
              ).then((_) => _loadDocuments());
            },
            tooltip: 'Demander un certificat',
          ),
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadDocuments,
          ),
        ],
      ),
      body: Column(
        children: [
          // Filter chips
          SizedBox(
            height: 60,
            child: ListView.builder(
              scrollDirection: Axis.horizontal,
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
              itemCount: _documentTypes.length,
              itemBuilder: (context, index) {
                final type = _documentTypes[index];
                final isSelected = _filterType == type['value'];
                return Padding(
                  padding: const EdgeInsets.only(right: 8),
                  child: FilterChip(
                    label: Text(type['label']!),
                    selected: isSelected,
                    onSelected: (selected) {
                      setState(() {
                        _filterType = type['value']!;
                      });
                    },
                  ),
                );
              },
            ),
          ),
          const Divider(height: 1),
          // Documents list
          Expanded(
            child: _buildBody(),
          ),
        ],
      ),
    );
  }

  Widget _buildBody() {
    if (_isLoading) {
      return const Center(child: CircularProgressIndicator());
    }

    if (_error != null) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(Icons.error_outline, size: 64, color: Colors.red),
            const SizedBox(height: 16),
            Text('Erreur: $_error'),
            const SizedBox(height: 16),
            ElevatedButton(
              onPressed: _loadDocuments,
              child: const Text('Réessayer'),
            ),
          ],
        ),
      );
    }

    final filteredDocs = _filteredDocuments;

    if (filteredDocs.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.folder_open, size: 64, color: Colors.grey[400]),
            const SizedBox(height: 16),
            Text(
              'Aucun document',
              style: TextStyle(fontSize: 18, color: Colors.grey[600]),
            ),
            const SizedBox(height: 8),
            Text(
              'Les documents de votre enfant apparaîtront ici',
              style: TextStyle(color: Colors.grey[500]),
            ),
          ],
        ),
      );
    }

    return RefreshIndicator(
      onRefresh: _loadDocuments,
      child: ListView.builder(
        padding: const EdgeInsets.all(16),
        itemCount: filteredDocs.length,
        itemBuilder: (context, index) {
          return _buildDocumentCard(filteredDocs[index]);
        },
      ),
    );
  }

  Widget _buildDocumentCard(Document document) {
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      child: InkWell(
        onTap: () {
          Navigator.push(
            context,
            MaterialPageRoute(
              builder: (context) => DocumentViewerScreen(document: document),
            ),
          );
        },
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  _getDocumentIcon(document),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          document.title,
                          style: const TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        if (document.description != null) ...[
                          const SizedBox(height: 4),
                          Text(
                            document.description!,
                            style: TextStyle(
                              fontSize: 14,
                              color: Colors.grey[600],
                            ),
                            maxLines: 2,
                            overflow: TextOverflow.ellipsis,
                          ),
                        ],
                      ],
                    ),
                  ),
                  if (document.isOfficial)
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                      decoration: BoxDecoration(
                        color: Colors.blue[100],
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: const Text(
                        'Officiel',
                        style: TextStyle(
                          fontSize: 12,
                          color: Colors.blue,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ),
                ],
              ),
              const SizedBox(height: 12),
              Row(
                children: [
                  Icon(Icons.insert_drive_file, size: 16, color: Colors.grey[600]),
                  const SizedBox(width: 4),
                  Text(
                    document.fileExtension,
                    style: TextStyle(fontSize: 12, color: Colors.grey[600]),
                  ),
                  const SizedBox(width: 16),
                  Icon(Icons.data_usage, size: 16, color: Colors.grey[600]),
                  const SizedBox(width: 4),
                  Text(
                    document.formattedFileSize,
                    style: TextStyle(fontSize: 12, color: Colors.grey[600]),
                  ),
                  const Spacer(),
                  if (document.issueDate != null)
                    Text(
                      DateFormat('dd/MM/yyyy').format(document.issueDate!),
                      style: TextStyle(fontSize: 12, color: Colors.grey[600]),
                    ),
                ],
              ),
              if (document.isExpired) ...[
                const SizedBox(height: 8),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                    color: Colors.red[100],
                    borderRadius: BorderRadius.circular(4),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(Icons.warning, size: 16, color: Colors.red[700]),
                      const SizedBox(width: 4),
                      Text(
                        'Expiré le ${DateFormat('dd/MM/yyyy').format(document.expiryDate!)}',
                        style: TextStyle(fontSize: 12, color: Colors.red[700]),
                      ),
                    ],
                  ),
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }

  Widget _getDocumentIcon(Document document) {
    IconData icon;
    Color color;

    if (document.isPdf) {
      icon = Icons.picture_as_pdf;
      color = Colors.red;
    } else if (document.isImage) {
      icon = Icons.image;
      color = Colors.blue;
    } else if (document.isWord) {
      icon = Icons.description;
      color = Colors.blue[700]!;
    } else if (document.isExcel) {
      icon = Icons.table_chart;
      color = Colors.green;
    } else {
      icon = Icons.insert_drive_file;
      color = Colors.grey;
    }

    return Container(
      padding: const EdgeInsets.all(10),
      decoration: BoxDecoration(
        color: color.withOpacity(0.1),
        borderRadius: BorderRadius.circular(8),
      ),
      child: Icon(icon, color: color, size: 28),
    );
  }
}
