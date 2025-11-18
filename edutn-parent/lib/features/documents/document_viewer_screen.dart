import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:url_launcher/url_launcher.dart';
import 'package:share_plus/share_plus.dart';
import 'document_model.dart';

class DocumentViewerScreen extends StatefulWidget {
  final Document document;

  const DocumentViewerScreen({
    Key? key,
    required this.document,
  }) : super(key: key);

  @override
  State<DocumentViewerScreen> createState() => _DocumentViewerScreenState();
}

class _DocumentViewerScreenState extends State<DocumentViewerScreen> {
  bool _isDownloading = false;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Document'),
        actions: [
          IconButton(
            icon: const Icon(Icons.share),
            onPressed: _shareDocument,
            tooltip: 'Partager',
          ),
          IconButton(
            icon: _isDownloading
                ? const SizedBox(
                    width: 20,
                    height: 20,
                    child: CircularProgressIndicator(
                      strokeWidth: 2,
                      valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                    ),
                  )
                : const Icon(Icons.download),
            onPressed: _isDownloading ? null : _downloadDocument,
            tooltip: 'Télécharger',
          ),
        ],
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Document icon and title
            Center(
              child: Column(
                children: [
                  _buildDocumentIcon(),
                  const SizedBox(height: 16),
                  Text(
                    widget.document.title,
                    style: const TextStyle(
                      fontSize: 24,
                      fontWeight: FontWeight.bold,
                    ),
                    textAlign: TextAlign.center,
                  ),
                  if (widget.document.titleAr != null) ...[
                    const SizedBox(height: 4),
                    Text(
                      widget.document.titleAr!,
                      style: const TextStyle(
                        fontSize: 20,
                        fontWeight: FontWeight.w500,
                      ),
                      textAlign: TextAlign.center,
                      textDirection: TextDirection.rtl,
                    ),
                  ],
                ],
              ),
            ),
            const SizedBox(height: 24),

            // Status badges
            Wrap(
              spacing: 8,
              runSpacing: 8,
              children: [
                if (widget.document.isOfficial)
                  _buildBadge(
                    'Document Officiel',
                    Colors.blue,
                    Icons.verified,
                  ),
                if (widget.document.requiresSignature)
                  _buildBadge(
                    'Signature Requise',
                    Colors.orange,
                    Icons.draw,
                  ),
                if (widget.document.isExpired)
                  _buildBadge(
                    'Expiré',
                    Colors.red,
                    Icons.warning,
                  ),
                _buildBadge(
                  widget.document.status.toUpperCase(),
                  _getStatusColor(),
                  Icons.info,
                ),
              ],
            ),
            const SizedBox(height: 24),

            // Description
            if (widget.document.description != null) ...[
              const Text(
                'Description',
                style: TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.bold,
                ),
              ),
              const SizedBox(height: 8),
              Text(
                widget.document.description!,
                style: const TextStyle(fontSize: 16),
              ),
              if (widget.document.descriptionAr != null) ...[
                const SizedBox(height: 8),
                Text(
                  widget.document.descriptionAr!,
                  style: const TextStyle(fontSize: 16),
                  textDirection: TextDirection.rtl,
                ),
              ],
              const SizedBox(height: 24),
            ],

            // Document details
            const Text(
              'Détails',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 12),
            _buildDetailRow('Type', _getDocumentTypeLabel()),
            if (widget.document.documentNumber != null)
              _buildDetailRow('Numéro', widget.document.documentNumber!),
            if (widget.document.issueDate != null)
              _buildDetailRow(
                'Date d\'émission',
                DateFormat('dd/MM/yyyy').format(widget.document.issueDate!),
              ),
            if (widget.document.expiryDate != null)
              _buildDetailRow(
                'Date d\'expiration',
                DateFormat('dd/MM/yyyy').format(widget.document.expiryDate!),
              ),
            _buildDetailRow('Format', widget.document.fileExtension),
            _buildDetailRow('Taille', widget.document.formattedFileSize),
            _buildDetailRow(
              'Téléchargements',
              widget.document.downloadCount.toString(),
            ),
            if (widget.document.lastDownloadedAt != null)
              _buildDetailRow(
                'Dernier téléchargement',
                DateFormat('dd/MM/yyyy HH:mm').format(widget.document.lastDownloadedAt!),
              ),
            _buildDetailRow(
              'Ajouté le',
              DateFormat('dd/MM/yyyy HH:mm').format(widget.document.createdAt),
            ),

            // Tags
            if (widget.document.tags != null && widget.document.tags!.isNotEmpty) ...[
              const SizedBox(height: 24),
              const Text(
                'Tags',
                style: TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.bold,
                ),
              ),
              const SizedBox(height: 12),
              Wrap(
                spacing: 8,
                runSpacing: 8,
                children: widget.document.tags!.map((tag) {
                  return Chip(
                    label: Text(tag),
                    backgroundColor: Colors.grey[200],
                  );
                }).toList(),
              ),
            ],

            const SizedBox(height: 32),

            // Action buttons
            SizedBox(
              width: double.infinity,
              child: ElevatedButton.icon(
                onPressed: _isDownloading ? null : _downloadDocument,
                icon: _isDownloading
                    ? const SizedBox(
                        width: 20,
                        height: 20,
                        child: CircularProgressIndicator(
                          strokeWidth: 2,
                          valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                        ),
                      )
                    : const Icon(Icons.download),
                label: Text(_isDownloading ? 'Téléchargement...' : 'Télécharger'),
                style: ElevatedButton.styleFrom(
                  padding: const EdgeInsets.all(16),
                ),
              ),
            ),
            const SizedBox(height: 12),
            SizedBox(
              width: double.infinity,
              child: OutlinedButton.icon(
                onPressed: _openDocument,
                icon: const Icon(Icons.open_in_new),
                label: const Text('Ouvrir dans le navigateur'),
                style: OutlinedButton.styleFrom(
                  padding: const EdgeInsets.all(16),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildDocumentIcon() {
    IconData icon;
    Color color;

    if (widget.document.isPdf) {
      icon = Icons.picture_as_pdf;
      color = Colors.red;
    } else if (widget.document.isImage) {
      icon = Icons.image;
      color = Colors.blue;
    } else if (widget.document.isWord) {
      icon = Icons.description;
      color = Colors.blue[700]!;
    } else if (widget.document.isExcel) {
      icon = Icons.table_chart;
      color = Colors.green;
    } else {
      icon = Icons.insert_drive_file;
      color = Colors.grey;
    }

    return Container(
      padding: const EdgeInsets.all(32),
      decoration: BoxDecoration(
        color: color.withOpacity(0.1),
        shape: BoxShape.circle,
      ),
      child: Icon(icon, color: color, size: 64),
    );
  }

  Widget _buildBadge(String label, Color color, IconData icon) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
      decoration: BoxDecoration(
        color: color.withOpacity(0.1),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: color.withOpacity(0.3)),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 16, color: color),
          const SizedBox(width: 4),
          Text(
            label,
            style: TextStyle(
              fontSize: 12,
              color: color,
              fontWeight: FontWeight.bold,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildDetailRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 150,
            child: Text(
              label,
              style: TextStyle(
                fontSize: 14,
                color: Colors.grey[600],
                fontWeight: FontWeight.w500,
              ),
            ),
          ),
          Expanded(
            child: Text(
              value,
              style: const TextStyle(
                fontSize: 14,
                fontWeight: FontWeight.w600,
              ),
            ),
          ),
        ],
      ),
    );
  }

  String _getDocumentTypeLabel() {
    switch (widget.document.documentType) {
      case 'certificate':
        return 'Certificat';
      case 'report':
        return 'Bulletin';
      case 'authorization':
        return 'Autorisation';
      case 'medical':
        return 'Document médical';
      case 'insurance':
        return 'Assurance';
      default:
        return 'Autre';
    }
  }

  Color _getStatusColor() {
    switch (widget.document.status.toLowerCase()) {
      case 'active':
        return Colors.green;
      case 'pending':
        return Colors.orange;
      case 'expired':
        return Colors.red;
      default:
        return Colors.grey;
    }
  }

  Future<void> _downloadDocument() async {
    setState(() => _isDownloading = true);

    try {
      // In a real app, you would download the file here
      // For now, we'll just open it in the browser
      final url = Uri.parse('${ApiClient.baseUrl}/storage/${widget.document.filePath}');
      if (await canLaunchUrl(url)) {
        await launchUrl(url, mode: LaunchMode.externalApplication);

        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('Document ouvert avec succès')),
          );
        }
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Erreur: $e')),
        );
      }
    } finally {
      if (mounted) {
        setState(() => _isDownloading = false);
      }
    }
  }

  Future<void> _openDocument() async {
    try {
      final url = Uri.parse('${ApiClient.baseUrl}/storage/${widget.document.filePath}');
      if (await canLaunchUrl(url)) {
        await launchUrl(url, mode: LaunchMode.externalApplication);
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Erreur: $e')),
        );
      }
    }
  }

  Future<void> _shareDocument() async {
    try {
      final url = '${ApiClient.baseUrl}/storage/${widget.document.filePath}';
      await Share.share(
        '${widget.document.title}\n\n$url',
        subject: widget.document.title,
      );
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Erreur: $e')),
        );
      }
    }
  }
}

class ApiClient {
  static const String baseUrl = 'https://api.edutnpro.tn';
}
