import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../../core/api/api_client.dart';

class ReportCardsScreen extends StatefulWidget {
  const ReportCardsScreen({Key? key}) : super(key: key);

  @override
  State<ReportCardsScreen> createState() => _ReportCardsScreenState();
}

class _ReportCardsScreenState extends State<ReportCardsScreen> {
  final ApiClient _apiClient = ApiClient();
  ReportCard? _reportCard;
  bool _isLoading = true;
  String? _error;
  String _selectedTerm = 'T1';

  @override
  void initState() {
    super.initState();
    _loadReportCard();
  }

  Future<void> _loadReportCard() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      final data = await _apiClient.getReportCard(_selectedTerm);
      setState(() {
        _reportCard = ReportCard.fromJson(data);
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _error = e.toString();
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Bulletins'),
        actions: [
          PopupMenuButton<String>(
            initialValue: _selectedTerm,
            onSelected: (value) {
              setState(() => _selectedTerm = value);
              _loadReportCard();
            },
            itemBuilder: (context) => [
              const PopupMenuItem(value: 'T1', child: Text('Trimestre 1')),
              const PopupMenuItem(value: 'T2', child: Text('Trimestre 2')),
              const PopupMenuItem(value: 'T3', child: Text('Trimestre 3')),
            ],
          ),
          if (_reportCard != null)
            IconButton(
              icon: const Icon(Icons.download),
              onPressed: () {
                // Download PDF
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(content: Text('Téléchargement du bulletin...')),
                );
              },
            ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? _buildErrorView()
              : _reportCard == null
                  ? const Center(child: Text('Aucun bulletin disponible'))
                  : RefreshIndicator(
                      onRefresh: _loadReportCard,
                      child: SingleChildScrollView(
                        physics: const AlwaysScrollableScrollPhysics(),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            _buildHeader(),
                            _buildOverallStats(),
                            _buildGradesTable(),
                            _buildAppreciationSection(),
                            _buildProgressComparison(),
                          ],
                        ),
                      ),
                    ),
    );
  }

  Widget _buildErrorView() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          const Icon(Icons.error_outline, size: 64, color: Colors.red),
          const SizedBox(height: 16),
          Text('Erreur: $_error'),
          const SizedBox(height: 16),
          ElevatedButton(
            onPressed: _loadReportCard,
            child: const Text('Réessayer'),
          ),
        ],
      ),
    );
  }

  Widget _buildHeader() {
    return Container(
      width: double.infinity,
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [Colors.blue.shade700, Colors.blue.shade500],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
      ),
      padding: const EdgeInsets.all(24),
      child: Column(
        children: [
          const Icon(Icons.school, size: 64, color: Colors.white),
          const SizedBox(height: 16),
          Text(
            'Bulletin Scolaire',
            style: const TextStyle(
              color: Colors.white,
              fontSize: 24,
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            _getTermLabel(_selectedTerm),
            style: const TextStyle(
              color: Colors.white70,
              fontSize: 16,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            _reportCard!.academicYear,
            style: const TextStyle(
              color: Colors.white70,
              fontSize: 14,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildOverallStats() {
    return Card(
      margin: const EdgeInsets.all(16),
      child: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceAround,
              children: [
                _buildStatColumn(
                  'Moyenne générale',
                  _reportCard!.overallAverage.toStringAsFixed(2),
                  Colors.blue,
                ),
                _buildStatColumn(
                  'Rang',
                  _reportCard!.rank != null
                      ? '${_reportCard!.rank}/${_reportCard!.totalStudents}'
                      : '-',
                  Colors.orange,
                ),
              ],
            ),
            const SizedBox(height: 16),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
              decoration: BoxDecoration(
                color: _getAppreciationColor(_reportCard!.appreciation).withOpacity(0.1),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(
                    _getAppreciationIcon(_reportCard!.appreciation),
                    color: _getAppreciationColor(_reportCard!.appreciation),
                  ),
                  const SizedBox(width: 8),
                  Text(
                    _reportCard!.appreciation,
                    style: TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                      color: _getAppreciationColor(_reportCard!.appreciation),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildStatColumn(String label, String value, Color color) {
    return Column(
      children: [
        Text(
          value,
          style: TextStyle(
            fontSize: 32,
            fontWeight: FontWeight.bold,
            color: color,
          ),
        ),
        Text(
          label,
          style: TextStyle(fontSize: 14, color: Colors.grey[600]),
        ),
      ],
    );
  }

  Widget _buildGradesTable() {
    return Card(
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Padding(
            padding: const EdgeInsets.all(16),
            child: const Text(
              'Détails des notes',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
          ),
          SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            child: DataTable(
              columns: const [
                DataColumn(label: Text('Matière', style: TextStyle(fontWeight: FontWeight.bold))),
                DataColumn(label: Text('Coef.', style: TextStyle(fontWeight: FontWeight.bold))),
                DataColumn(label: Text('Moyenne', style: TextStyle(fontWeight: FontWeight.bold))),
                DataColumn(label: Text('Classe', style: TextStyle(fontWeight: FontWeight.bold))),
              ],
              rows: _reportCard!.subjectGrades.map((grade) {
                return DataRow(
                  cells: [
                    DataCell(
                      SizedBox(
                        width: 120,
                        child: Text(
                          grade.subjectName,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                    ),
                    DataCell(Text(grade.coefficient.toStringAsFixed(1))),
                    DataCell(
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                        decoration: BoxDecoration(
                          color: _getGradeColor(grade.average).withOpacity(0.2),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: Text(
                          grade.average.toStringAsFixed(2),
                          style: TextStyle(
                            color: _getGradeColor(grade.average),
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ),
                    ),
                    DataCell(
                      Text(
                        grade.classAverage?.toStringAsFixed(2) ?? '-',
                        style: TextStyle(color: Colors.grey[600]),
                      ),
                    ),
                  ],
                );
              }).toList(),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildAppreciationSection() {
    if (_reportCard!.teacherComments.isEmpty && _reportCard!.generalComment == null) {
      return const SizedBox.shrink();
    }

    return Card(
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Appréciations',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 12),
            if (_reportCard!.generalComment != null) ...[
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: Colors.blue.shade50,
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Icon(Icons.comment, color: Colors.blue.shade700),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'Appréciation générale',
                            style: TextStyle(
                              fontWeight: FontWeight.bold,
                              color: Colors.blue.shade900,
                            ),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            _reportCard!.generalComment!,
                            style: TextStyle(color: Colors.blue.shade900),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 12),
            ],
            ...(_reportCard!.teacherComments.map((comment) {
              return Padding(
                padding: const EdgeInsets.only(bottom: 8),
                child: Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: Colors.grey.shade100,
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Icon(Icons.person, color: Colors.grey.shade700),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              comment.subjectName,
                              style: TextStyle(
                                fontWeight: FontWeight.bold,
                                color: Colors.grey.shade900,
                              ),
                            ),
                            const SizedBox(height: 4),
                            Text(
                              comment.comment,
                              style: TextStyle(color: Colors.grey.shade800),
                            ),
                            if (comment.teacherName != null) ...[
                              const SizedBox(height: 4),
                              Text(
                                '- ${comment.teacherName}',
                                style: TextStyle(
                                  fontSize: 12,
                                  fontStyle: FontStyle.italic,
                                  color: Colors.grey.shade600,
                                ),
                              ),
                            ],
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
              );
            }).toList()),
          ],
        ),
      ),
    );
  }

  Widget _buildProgressComparison() {
    if (_reportCard!.previousTermAverage == null) {
      return const SizedBox.shrink();
    }

    final difference = _reportCard!.overallAverage - _reportCard!.previousTermAverage!;
    final isImprovement = difference > 0;

    return Card(
      margin: const EdgeInsets.all(16),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Évolution',
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 12),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceAround,
              children: [
                Column(
                  children: [
                    Text(
                      'Trimestre précédent',
                      style: TextStyle(fontSize: 12, color: Colors.grey[600]),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      _reportCard!.previousTermAverage!.toStringAsFixed(2),
                      style: const TextStyle(
                        fontSize: 24,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ],
                ),
                Icon(
                  isImprovement ? Icons.trending_up : Icons.trending_down,
                  size: 48,
                  color: isImprovement ? Colors.green : Colors.red,
                ),
                Column(
                  children: [
                    Text(
                      'Ce trimestre',
                      style: TextStyle(fontSize: 12, color: Colors.grey[600]),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      _reportCard!.overallAverage.toStringAsFixed(2),
                      style: TextStyle(
                        fontSize: 24,
                        fontWeight: FontWeight.bold,
                        color: isImprovement ? Colors.green : Colors.red,
                      ),
                    ),
                  ],
                ),
              ],
            ),
            const SizedBox(height: 12),
            Center(
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                decoration: BoxDecoration(
                  color: (isImprovement ? Colors.green : Colors.red).withOpacity(0.1),
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Text(
                  '${isImprovement ? '+' : ''}${difference.toStringAsFixed(2)} points',
                  style: TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.bold,
                    color: isImprovement ? Colors.green : Colors.red,
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Color _getGradeColor(double average) {
    if (average >= 16) return Colors.green;
    if (average >= 14) return Colors.lightGreen;
    if (average >= 12) return Colors.blue;
    if (average >= 10) return Colors.orange;
    return Colors.red;
  }

  Color _getAppreciationColor(String appreciation) {
    if (appreciation.contains('Excellent') || appreciation.contains('Très bien')) {
      return Colors.green;
    } else if (appreciation.contains('Bien')) {
      return Colors.lightGreen;
    } else if (appreciation.contains('Assez bien')) {
      return Colors.blue;
    } else if (appreciation.contains('Passable')) {
      return Colors.orange;
    }
    return Colors.red;
  }

  IconData _getAppreciationIcon(String appreciation) {
    if (appreciation.contains('Excellent') || appreciation.contains('Très bien')) {
      return Icons.star;
    } else if (appreciation.contains('Bien')) {
      return Icons.thumb_up;
    } else if (appreciation.contains('Assez bien')) {
      return Icons.thumbs_up_down;
    } else if (appreciation.contains('Passable')) {
      return Icons.warning;
    }
    return Icons.error;
  }

  String _getTermLabel(String term) {
    switch (term) {
      case 'T1':
        return 'Premier trimestre';
      case 'T2':
        return 'Deuxième trimestre';
      case 'T3':
        return 'Troisième trimestre';
      default:
        return term;
    }
  }
}

class ReportCard {
  final int id;
  final String term;
  final String academicYear;
  final double overallAverage;
  final int? rank;
  final int? totalStudents;
  final String appreciation;
  final String? generalComment;
  final double? previousTermAverage;
  final List<SubjectReportGrade> subjectGrades;
  final List<TeacherComment> teacherComments;

  ReportCard({
    required this.id,
    required this.term,
    required this.academicYear,
    required this.overallAverage,
    this.rank,
    this.totalStudents,
    required this.appreciation,
    this.generalComment,
    this.previousTermAverage,
    required this.subjectGrades,
    required this.teacherComments,
  });

  factory ReportCard.fromJson(Map<String, dynamic> json) {
    return ReportCard(
      id: json['id'],
      term: json['term'] ?? '',
      academicYear: json['academic_year'] ?? '',
      overallAverage: (json['overall_average'] ?? 0).toDouble(),
      rank: json['rank'],
      totalStudents: json['total_students'],
      appreciation: json['appreciation'] ?? '',
      generalComment: json['general_comment'],
      previousTermAverage: json['previous_term_average'] != null
          ? (json['previous_term_average'] as num).toDouble()
          : null,
      subjectGrades: (json['subject_grades'] as List? ?? [])
          .map((g) => SubjectReportGrade.fromJson(g))
          .toList(),
      teacherComments: (json['teacher_comments'] as List? ?? [])
          .map((c) => TeacherComment.fromJson(c))
          .toList(),
    );
  }
}

class SubjectReportGrade {
  final int subjectId;
  final String subjectName;
  final double coefficient;
  final double average;
  final double? classAverage;

  SubjectReportGrade({
    required this.subjectId,
    required this.subjectName,
    required this.coefficient,
    required this.average,
    this.classAverage,
  });

  factory SubjectReportGrade.fromJson(Map<String, dynamic> json) {
    return SubjectReportGrade(
      subjectId: json['subject_id'],
      subjectName: json['subject_name'] ?? '',
      coefficient: (json['coefficient'] ?? 1).toDouble(),
      average: (json['average'] ?? 0).toDouble(),
      classAverage: json['class_average'] != null
          ? (json['class_average'] as num).toDouble()
          : null,
    );
  }
}

class TeacherComment {
  final int subjectId;
  final String subjectName;
  final String comment;
  final String? teacherName;

  TeacherComment({
    required this.subjectId,
    required this.subjectName,
    required this.comment,
    this.teacherName,
  });

  factory TeacherComment.fromJson(Map<String, dynamic> json) {
    return TeacherComment(
      subjectId: json['subject_id'],
      subjectName: json['subject_name'] ?? '',
      comment: json['comment'] ?? '',
      teacherName: json['teacher_name'],
    );
  }
}
