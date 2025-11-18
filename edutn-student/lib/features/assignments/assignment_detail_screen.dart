import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../core/api/api_client.dart';
import 'assignment_model.dart';

class AssignmentDetailScreen extends StatefulWidget {
  final Assignment assignment;

  const AssignmentDetailScreen({
    Key? key,
    required this.assignment,
  }) : super(key: key);

  @override
  State<AssignmentDetailScreen> createState() => _AssignmentDetailScreenState();
}

class _AssignmentDetailScreenState extends State<AssignmentDetailScreen> {
  final ApiClient _apiClient = ApiClient();
  final TextEditingController _contentController = TextEditingController();
  bool _isSubmitting = false;
  String? _selectedFilePath;

  @override
  void dispose() {
    _contentController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final assignment = widget.assignment;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Détail du devoir'),
      ),
      body: SingleChildScrollView(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Header card
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  colors: [_getTypeColor(assignment.type), _getTypeColor(assignment.type).withOpacity(0.7)],
                ),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                        decoration: BoxDecoration(
                          color: Colors.white.withOpacity(0.3),
                          borderRadius: BorderRadius.circular(16),
                        ),
                        child: Text(
                          assignment.typeLabel,
                          style: const TextStyle(
                            color: Colors.white,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ),
                      const Spacer(),
                      if (assignment.maxScore != null)
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                          decoration: BoxDecoration(
                            color: Colors.white.withOpacity(0.3),
                            borderRadius: BorderRadius.circular(16),
                          ),
                          child: Text(
                            'Sur ${assignment.maxScore} pts',
                            style: const TextStyle(
                              color: Colors.white,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ),
                    ],
                  ),
                  const SizedBox(height: 16),
                  Text(
                    assignment.title,
                    style: const TextStyle(
                      fontSize: 24,
                      fontWeight: FontWeight.bold,
                      color: Colors.white,
                    ),
                  ),
                  const SizedBox(height: 8),
                  Row(
                    children: [
                      const Icon(Icons.book, color: Colors.white70, size: 18),
                      const SizedBox(width: 6),
                      Text(
                        assignment.subjectName ?? '',
                        style: const TextStyle(
                          color: Colors.white70,
                          fontSize: 16,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 4),
                  Row(
                    children: [
                      const Icon(Icons.person, color: Colors.white70, size: 18),
                      const SizedBox(width: 6),
                      Text(
                        assignment.teacherName ?? '',
                        style: const TextStyle(
                          color: Colors.white70,
                          fontSize: 16,
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),

            Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Due date card
                  Card(
                    color: assignment.isOverdue ? Colors.red[50] : Colors.blue[50],
                    child: Padding(
                      padding: const EdgeInsets.all(16),
                      child: Row(
                        children: [
                          Icon(
                            Icons.calendar_today,
                            color: assignment.isOverdue ? Colors.red : Colors.blue,
                            size: 32,
                          ),
                          const SizedBox(width: 16),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  assignment.isOverdue ? 'Date limite dépassée' : 'À rendre pour le',
                                  style: TextStyle(
                                    fontSize: 14,
                                    color: assignment.isOverdue ? Colors.red[700] : Colors.blue[700],
                                  ),
                                ),
                                const SizedBox(height: 4),
                                Text(
                                  DateFormat('EEEE dd MMMM yyyy à HH:mm', 'fr').format(assignment.dueDate),
                                  style: TextStyle(
                                    fontSize: 16,
                                    fontWeight: FontWeight.bold,
                                    color: assignment.isOverdue ? Colors.red[700] : Colors.blue[700],
                                  ),
                                ),
                                if (!assignment.isOverdue && !assignment.isSubmitted) ...[
                                  const SizedBox(height: 4),
                                  Text(
                                    'Dans ${assignment.daysUntilDue} jour${assignment.daysUntilDue > 1 ? 's' : ''}',
                                    style: TextStyle(
                                      fontSize: 14,
                                      color: assignment.daysUntilDue <= 2 ? Colors.orange : Colors.grey[600],
                                      fontWeight: assignment.daysUntilDue <= 2 ? FontWeight.bold : FontWeight.normal,
                                    ),
                                  ),
                                ],
                              ],
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),

                  const SizedBox(height: 24),

                  // Description
                  if (assignment.description != null) ...[
                    const Text(
                      'Description',
                      style: TextStyle(
                        fontSize: 20,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 12),
                    Container(
                      width: double.infinity,
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(
                        color: Colors.grey[100],
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Text(
                        assignment.description!,
                        style: const TextStyle(fontSize: 16),
                      ),
                    ),
                    const SizedBox(height: 24),
                  ],

                  // Attachment
                  if (assignment.attachmentUrl != null) ...[
                    const Text(
                      'Pièce jointe',
                      style: TextStyle(
                        fontSize: 20,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 12),
                    InkWell(
                      onTap: () => _openAttachment(assignment.attachmentUrl!),
                      child: Container(
                        padding: const EdgeInsets.all(16),
                        decoration: BoxDecoration(
                          border: Border.all(color: Colors.blue),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: Row(
                          children: [
                            const Icon(Icons.attach_file, color: Colors.blue),
                            const SizedBox(width: 12),
                            Expanded(
                              child: Text(
                                assignment.attachmentUrl!.split('/').last,
                                style: const TextStyle(
                                  color: Colors.blue,
                                  fontSize: 16,
                                ),
                              ),
                            ),
                            const Icon(Icons.download, color: Colors.blue),
                          ],
                        ),
                      ),
                    ),
                    const SizedBox(height: 24),
                  ],

                  // Submission status or form
                  if (assignment.isSubmitted)
                    _buildSubmissionView(assignment.mySubmission!)
                  else
                    _buildSubmissionForm(),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildSubmissionView(AssignmentSubmission submission) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'Ma soumission',
          style: TextStyle(
            fontSize: 20,
            fontWeight: FontWeight.bold,
          ),
        ),
        const SizedBox(height: 12),
        Container(
          width: double.infinity,
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: submission.isGraded ? Colors.green[50] : Colors.blue[50],
            borderRadius: BorderRadius.circular(8),
            border: Border.all(
              color: submission.isGraded ? Colors.green : Colors.blue,
              width: 2,
            ),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Icon(
                    submission.isGraded ? Icons.grade : Icons.check_circle,
                    color: submission.isGraded ? Colors.green : Colors.blue,
                    size: 32,
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          submission.isGraded ? 'Devoir noté' : 'Devoir soumis',
                          style: TextStyle(
                            fontSize: 18,
                            fontWeight: FontWeight.bold,
                            color: submission.isGraded ? Colors.green[700] : Colors.blue[700],
                          ),
                        ),
                        Text(
                          'Le ${DateFormat('dd/MM/yyyy à HH:mm').format(submission.submittedAt)}',
                          style: TextStyle(
                            fontSize: 14,
                            color: Colors.grey[600],
                          ),
                        ),
                      ],
                    ),
                  ),
                  if (submission.isGraded)
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                      decoration: BoxDecoration(
                        color: Colors.green,
                        borderRadius: BorderRadius.circular(20),
                      ),
                      child: Text(
                        '${submission.grade}/${submission.maxScore}',
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 20,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ),
                ],
              ),
              if (submission.isLate) ...[
                const SizedBox(height: 12),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                  decoration: BoxDecoration(
                    color: Colors.red[100],
                    borderRadius: BorderRadius.circular(16),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(Icons.warning, size: 16, color: Colors.red[700]),
                      const SizedBox(width: 6),
                      Text(
                        'Soumis en retard',
                        style: TextStyle(
                          color: Colors.red[700],
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
              if (submission.content != null) ...[
                const SizedBox(height: 16),
                const Divider(),
                const SizedBox(height: 12),
                const Text(
                  'Mon texte:',
                  style: TextStyle(
                    fontWeight: FontWeight.bold,
                    fontSize: 16,
                  ),
                ),
                const SizedBox(height: 8),
                Text(submission.content!),
              ],
              if (submission.attachmentUrl != null) ...[
                const SizedBox(height: 16),
                InkWell(
                  onTap: () => _openAttachment(submission.attachmentUrl!),
                  child: Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      border: Border.all(color: Colors.grey),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Row(
                      children: [
                        const Icon(Icons.attach_file),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Text(submission.attachmentUrl!.split('/').last),
                        ),
                        const Icon(Icons.download),
                      ],
                    ),
                  ),
                ),
              ],
              if (submission.feedback != null) ...[
                const SizedBox(height: 16),
                const Divider(),
                const SizedBox(height: 12),
                const Text(
                  'Commentaire du professeur:',
                  style: TextStyle(
                    fontWeight: FontWeight.bold,
                    fontSize: 16,
                  ),
                ),
                const SizedBox(height: 8),
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: Colors.amber[50],
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(
                    submission.feedback!,
                    style: const TextStyle(fontSize: 15),
                  ),
                ),
              ],
            ],
          ),
        ),
      ],
    );
  }

  Widget _buildSubmissionForm() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'Soumettre mon devoir',
          style: TextStyle(
            fontSize: 20,
            fontWeight: FontWeight.bold,
          ),
        ),
        const SizedBox(height: 12),
        TextField(
          controller: _contentController,
          maxLines: 8,
          decoration: const InputDecoration(
            hintText: 'Écrivez votre réponse ici...',
            border: OutlineInputBorder(),
          ),
        ),
        const SizedBox(height: 16),
        OutlinedButton.icon(
          onPressed: _pickFile,
          icon: const Icon(Icons.attach_file),
          label: Text(_selectedFilePath == null ? 'Joindre un fichier' : 'Fichier sélectionné'),
        ),
        if (_selectedFilePath != null) ...[
          const SizedBox(height: 8),
          Text(
            _selectedFilePath!.split('/').last,
            style: TextStyle(color: Colors.grey[600]),
          ),
        ],
        const SizedBox(height: 24),
        SizedBox(
          width: double.infinity,
          height: 50,
          child: ElevatedButton(
            onPressed: _isSubmitting ? null : _submitAssignment,
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
                    'Soumettre le devoir',
                    style: TextStyle(fontSize: 16),
                  ),
          ),
        ),
      ],
    );
  }

  void _pickFile() {
    // TODO: Implement file picker
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(content: Text('Sélection de fichier - À implémenter')),
    );
  }

  Future<void> _submitAssignment() async {
    if (_contentController.text.trim().isEmpty && _selectedFilePath == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Veuillez saisir un texte ou joindre un fichier'),
          backgroundColor: Colors.red,
        ),
      );
      return;
    }

    setState(() => _isSubmitting = true);

    try {
      await _apiClient.submitAssignment(
        widget.assignment.id,
        content: _contentController.text.trim(),
        attachmentPath: _selectedFilePath,
      );

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Devoir soumis avec succès!'),
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

  Future<void> _openAttachment(String url) async {
    try {
      final uri = Uri.parse(url);
      if (await canLaunchUrl(uri)) {
        await launchUrl(uri, mode: LaunchMode.externalApplication);
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Erreur: $e')),
        );
      }
    }
  }

  Color _getTypeColor(String type) {
    switch (type) {
      case 'homework':
        return Colors.blue;
      case 'project':
        return Colors.purple;
      case 'exam':
        return Colors.red;
      case 'quiz':
        return Colors.orange;
      case 'presentation':
        return Colors.green;
      default:
        return Colors.grey;
    }
  }
}
