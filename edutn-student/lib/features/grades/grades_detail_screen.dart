import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:fl_chart/fl_chart.dart';
import '../../core/api/api_client.dart';

class GradesDetailScreen extends StatefulWidget {
  const GradesDetailScreen({Key? key}) : super(key: key);

  @override
  State<GradesDetailScreen> createState() => _GradesDetailScreenState();
}

class _GradesDetailScreenState extends State<GradesDetailScreen> {
  final ApiClient _apiClient = ApiClient();
  List<SubjectGrade> _grades = [];
  GradeStats? _stats;
  bool _isLoading = true;
  String? _error;
  String _selectedTerm = 'T1';

  @override
  void initState() {
    super.initState();
    _loadGrades();
  }

  Future<void> _loadGrades() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      final data = await _apiClient.getGrades(term: _selectedTerm);
      setState(() {
        _grades = (data['grades'] as List)
            .map((json) => SubjectGrade.fromJson(json))
            .toList();
        _stats = GradeStats.fromJson(data['stats']);
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
        title: const Text('Mes notes'),
        actions: [
          PopupMenuButton<String>(
            initialValue: _selectedTerm,
            onSelected: (value) {
              setState(() => _selectedTerm = value);
              _loadGrades();
            },
            itemBuilder: (context) => [
              const PopupMenuItem(value: 'T1', child: Text('Trimestre 1')),
              const PopupMenuItem(value: 'T2', child: Text('Trimestre 2')),
              const PopupMenuItem(value: 'T3', child: Text('Trimestre 3')),
            ],
          ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? _buildErrorView()
              : RefreshIndicator(
                  onRefresh: _loadGrades,
                  child: SingleChildScrollView(
                    physics: const AlwaysScrollableScrollPhysics(),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        if (_stats != null) ...[
                          _buildStatsCard(),
                          _buildProgressChart(),
                        ],
                        _buildGradesList(),
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
            onPressed: _loadGrades,
            child: const Text('Réessayer'),
          ),
        ],
      ),
    );
  }

  Widget _buildStatsCard() {
    return Card(
      margin: const EdgeInsets.all(16),
      child: Container(
        decoration: BoxDecoration(
          gradient: LinearGradient(
            colors: [Colors.blue.shade700, Colors.blue.shade500],
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
          ),
          borderRadius: BorderRadius.circular(12),
        ),
        padding: const EdgeInsets.all(20),
        child: Column(
          children: [
            const Text(
              'Moyenne générale',
              style: TextStyle(
                color: Colors.white70,
                fontSize: 16,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              _stats!.overallAverage.toStringAsFixed(2),
              style: const TextStyle(
                color: Colors.white,
                fontSize: 48,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 4),
            Text(
              '/ 20',
              style: const TextStyle(
                color: Colors.white70,
                fontSize: 16,
              ),
            ),
            const SizedBox(height: 16),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceAround,
              children: [
                _buildStatColumn(
                  'Matières',
                  _stats!.totalSubjects.toString(),
                  Icons.book,
                ),
                _buildStatColumn(
                  'Rang',
                  _stats!.rank != null ? '${_stats!.rank}/${_stats!.totalStudents}' : '-',
                  Icons.leaderboard,
                ),
                _buildStatColumn(
                  'Appréciation',
                  _stats!.appreciation,
                  Icons.star,
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildStatColumn(String label, String value, IconData icon) {
    return Column(
      children: [
        Icon(icon, color: Colors.white, size: 24),
        const SizedBox(height: 4),
        Text(
          value,
          style: const TextStyle(
            color: Colors.white,
            fontSize: 18,
            fontWeight: FontWeight.bold,
          ),
        ),
        Text(
          label,
          style: const TextStyle(
            color: Colors.white70,
            fontSize: 12,
          ),
        ),
      ],
    );
  }

  Widget _buildProgressChart() {
    if (_grades.isEmpty) return const SizedBox.shrink();

    return Card(
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Moyennes par matière',
              style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 16),
            SizedBox(
              height: 250,
              child: BarChart(
                BarChartData(
                  alignment: BarChartAlignment.spaceAround,
                  maxY: 20,
                  barTouchData: BarTouchData(enabled: false),
                  titlesData: FlTitlesData(
                    show: true,
                    bottomTitles: AxisTitles(
                      sideTitles: SideTitles(
                        showTitles: true,
                        getTitlesWidget: (value, meta) {
                          if (value.toInt() >= _grades.length) return const Text('');
                          return Padding(
                            padding: const EdgeInsets.only(top: 8),
                            child: Text(
                              _grades[value.toInt()].subjectCode,
                              style: const TextStyle(fontSize: 10),
                            ),
                          );
                        },
                      ),
                    ),
                    leftTitles: AxisTitles(
                      sideTitles: SideTitles(
                        showTitles: true,
                        reservedSize: 30,
                        getTitlesWidget: (value, meta) {
                          return Text(value.toInt().toString());
                        },
                      ),
                    ),
                    topTitles: AxisTitles(sideTitles: SideTitles(showTitles: false)),
                    rightTitles: AxisTitles(sideTitles: SideTitles(showTitles: false)),
                  ),
                  gridData: FlGridData(show: true, horizontalInterval: 5),
                  borderData: FlBorderData(show: false),
                  barGroups: _grades.asMap().entries.map((entry) {
                    final index = entry.key;
                    final grade = entry.value;
                    return BarChartGroupData(
                      x: index,
                      barRods: [
                        BarChartRodData(
                          toY: grade.average,
                          color: _getGradeColor(grade.average),
                          width: 20,
                          borderRadius: const BorderRadius.vertical(top: Radius.circular(4)),
                        ),
                      ],
                    );
                  }).toList(),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildGradesList() {
    if (_grades.isEmpty) {
      return const Center(
        child: Padding(
          padding: EdgeInsets.all(32),
          child: Text('Aucune note disponible'),
        ),
      );
    }

    return Padding(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Détails par matière',
            style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 12),
          ListView.builder(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            itemCount: _grades.length,
            itemBuilder: (context, index) {
              return _buildGradeCard(_grades[index]);
            },
          ),
        ],
      ),
    );
  }

  Widget _buildGradeCard(SubjectGrade grade) {
    final gradeColor = _getGradeColor(grade.average);

    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      child: InkWell(
        onTap: () => _showGradeDetails(grade),
        borderRadius: BorderRadius.circular(12),
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          grade.subjectName,
                          style: const TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        Text(
                          'Coefficient: ${grade.coefficient}',
                          style: TextStyle(fontSize: 12, color: Colors.grey[600]),
                        ),
                      ],
                    ),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                    decoration: BoxDecoration(
                      color: gradeColor.withOpacity(0.2),
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: Column(
                      children: [
                        Text(
                          grade.average.toStringAsFixed(2),
                          style: TextStyle(
                            fontSize: 24,
                            fontWeight: FontWeight.bold,
                            color: gradeColor,
                          ),
                        ),
                        Text(
                          '/20',
                          style: TextStyle(fontSize: 12, color: Colors.grey[600]),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 12),
              LinearProgressIndicator(
                value: grade.average / 20,
                backgroundColor: Colors.grey[300],
                valueColor: AlwaysStoppedAnimation<Color>(gradeColor),
                minHeight: 6,
              ),
              if (grade.teacherNote != null) ...[
                const SizedBox(height: 8),
                Row(
                  children: [
                    Icon(Icons.info_outline, size: 16, color: Colors.grey[600]),
                    const SizedBox(width: 4),
                    Expanded(
                      child: Text(
                        grade.teacherNote!,
                        style: TextStyle(fontSize: 12, color: Colors.grey[600]),
                      ),
                    ),
                  ],
                ),
              ],
            ],
          ),
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

  void _showGradeDetails(SubjectGrade grade) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (context) => DraggableScrollableSheet(
        initialChildSize: 0.7,
        maxChildSize: 0.9,
        minChildSize: 0.5,
        expand: false,
        builder: (context, scrollController) => SingleChildScrollView(
          controller: scrollController,
          padding: const EdgeInsets.all(24),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Center(
                child: Container(
                  width: 40,
                  height: 4,
                  decoration: BoxDecoration(
                    color: Colors.grey[300],
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
              ),
              const SizedBox(height: 24),
              Text(
                grade.subjectName,
                style: const TextStyle(fontSize: 24, fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 8),
              Text(
                'Coefficient: ${grade.coefficient}',
                style: TextStyle(fontSize: 14, color: Colors.grey[600]),
              ),
              const Divider(height: 32),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceAround,
                children: [
                  _buildDetailStat('Moyenne', grade.average.toStringAsFixed(2)),
                  _buildDetailStat('Plus haute', grade.highestGrade?.toStringAsFixed(2) ?? '-'),
                  _buildDetailStat('Plus basse', grade.lowestGrade?.toStringAsFixed(2) ?? '-'),
                ],
              ),
              const SizedBox(height: 24),
              const Text(
                'Toutes les notes',
                style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 12),
              ...grade.individualGrades.map((g) => _buildIndividualGradeTile(g)).toList(),
              if (grade.teacherNote != null) ...[
                const SizedBox(height: 16),
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: Colors.blue.shade50,
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Row(
                    children: [
                      Icon(Icons.comment, color: Colors.blue.shade700),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Text(
                          grade.teacherNote!,
                          style: TextStyle(color: Colors.blue.shade900),
                        ),
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

  Widget _buildDetailStat(String label, String value) {
    return Column(
      children: [
        Text(
          value,
          style: const TextStyle(
            fontSize: 24,
            fontWeight: FontWeight.bold,
            color: Colors.blue,
          ),
        ),
        Text(
          label,
          style: TextStyle(fontSize: 12, color: Colors.grey[600]),
        ),
      ],
    );
  }

  Widget _buildIndividualGradeTile(IndividualGrade grade) {
    return Card(
      margin: const EdgeInsets.only(bottom: 8),
      child: ListTile(
        leading: CircleAvatar(
          backgroundColor: _getGradeColor(grade.score).withOpacity(0.2),
          child: Text(
            grade.score.toStringAsFixed(1),
            style: TextStyle(
              color: _getGradeColor(grade.score),
              fontWeight: FontWeight.bold,
            ),
          ),
        ),
        title: Text(grade.type),
        subtitle: Text(DateFormat('dd/MM/yyyy').format(grade.date)),
        trailing: Text(
          '/${grade.maxScore.toStringAsFixed(0)}',
          style: TextStyle(color: Colors.grey[600]),
        ),
      ),
    );
  }
}

class SubjectGrade {
  final int subjectId;
  final String subjectName;
  final String subjectCode;
  final double coefficient;
  final double average;
  final double? highestGrade;
  final double? lowestGrade;
  final String? teacherNote;
  final List<IndividualGrade> individualGrades;

  SubjectGrade({
    required this.subjectId,
    required this.subjectName,
    required this.subjectCode,
    required this.coefficient,
    required this.average,
    this.highestGrade,
    this.lowestGrade,
    this.teacherNote,
    required this.individualGrades,
  });

  factory SubjectGrade.fromJson(Map<String, dynamic> json) {
    return SubjectGrade(
      subjectId: json['subject_id'],
      subjectName: json['subject_name'] ?? '',
      subjectCode: json['subject_code'] ?? '',
      coefficient: (json['coefficient'] ?? 1).toDouble(),
      average: (json['average'] ?? 0).toDouble(),
      highestGrade: json['highest_grade'] != null ? (json['highest_grade'] as num).toDouble() : null,
      lowestGrade: json['lowest_grade'] != null ? (json['lowest_grade'] as num).toDouble() : null,
      teacherNote: json['teacher_note'],
      individualGrades: (json['individual_grades'] as List? ?? [])
          .map((g) => IndividualGrade.fromJson(g))
          .toList(),
    );
  }
}

class IndividualGrade {
  final int id;
  final String type;
  final double score;
  final double maxScore;
  final DateTime date;

  IndividualGrade({
    required this.id,
    required this.type,
    required this.score,
    required this.maxScore,
    required this.date,
  });

  factory IndividualGrade.fromJson(Map<String, dynamic> json) {
    return IndividualGrade(
      id: json['id'],
      type: json['type'] ?? '',
      score: (json['score'] ?? 0).toDouble(),
      maxScore: (json['max_score'] ?? 20).toDouble(),
      date: DateTime.parse(json['date']),
    );
  }
}

class GradeStats {
  final double overallAverage;
  final int totalSubjects;
  final int? rank;
  final int? totalStudents;
  final String appreciation;

  GradeStats({
    required this.overallAverage,
    required this.totalSubjects,
    this.rank,
    this.totalStudents,
    required this.appreciation,
  });

  factory GradeStats.fromJson(Map<String, dynamic> json) {
    return GradeStats(
      overallAverage: (json['overall_average'] ?? 0).toDouble(),
      totalSubjects: json['total_subjects'] ?? 0,
      rank: json['rank'],
      totalStudents: json['total_students'],
      appreciation: json['appreciation'] ?? '',
    );
  }
}
