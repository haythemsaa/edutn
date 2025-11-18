import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:fl_chart/fl_chart.dart';
import '../../core/api/api_client.dart';
import 'attendance_model.dart';

class AttendanceScreen extends StatefulWidget {
  const AttendanceScreen({Key? key}) : super(key: key);

  @override
  State<AttendanceScreen> createState() => _AttendanceScreenState();
}

class _AttendanceScreenState extends State<AttendanceScreen> {
  final ApiClient _apiClient = ApiClient();
  List<AttendanceRecord> _records = [];
  AttendanceStats? _stats;
  bool _isLoading = true;
  String? _error;
  String _selectedMonth = DateFormat('yyyy-MM').format(DateTime.now());

  @override
  void initState() {
    super.initState();
    _loadAttendance();
  }

  Future<void> _loadAttendance() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      final data = await _apiClient.getAttendance(month: _selectedMonth);
      setState(() {
        _records = (data['records'] as List)
            .map((json) => AttendanceRecord.fromJson(json))
            .toList();
        _stats = AttendanceStats.fromJson(data['stats']);
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
        title: const Text('Assiduité'),
        actions: [
          IconButton(
            icon: const Icon(Icons.calendar_month),
            onPressed: _selectMonth,
          ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? _buildErrorView()
              : RefreshIndicator(
                  onRefresh: _loadAttendance,
                  child: SingleChildScrollView(
                    physics: const AlwaysScrollableScrollPhysics(),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        if (_stats != null) ...[
                          _buildStatsCard(),
                          _buildChart(),
                        ],
                        _buildRecordsList(),
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
            onPressed: _loadAttendance,
            child: const Text('Réessayer'),
          ),
        ],
      ),
    );
  }

  Widget _buildStatsCard() {
    return Card(
      margin: const EdgeInsets.all(16),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Statistiques - ${DateFormat.yMMMM('fr').format(DateTime.parse('$_selectedMonth-01'))}',
              style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 16),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                _buildStatItem(
                  'Présent',
                  _stats!.presentDays.toString(),
                  Colors.green,
                  Icons.check_circle,
                ),
                _buildStatItem(
                  'Absent',
                  _stats!.absentDays.toString(),
                  Colors.red,
                  Icons.cancel,
                ),
                _buildStatItem(
                  'En retard',
                  _stats!.lateDays.toString(),
                  Colors.orange,
                  Icons.schedule,
                ),
                _buildStatItem(
                  'Excusé',
                  _stats!.excusedDays.toString(),
                  Colors.blue,
                  Icons.event_note,
                ),
              ],
            ),
            const SizedBox(height: 16),
            LinearProgressIndicator(
              value: _stats!.attendanceRate / 100,
              backgroundColor: Colors.grey[300],
              valueColor: AlwaysStoppedAnimation<Color>(
                _stats!.attendanceRate >= 90
                    ? Colors.green
                    : _stats!.attendanceRate >= 75
                        ? Colors.orange
                        : Colors.red,
              ),
              minHeight: 8,
            ),
            const SizedBox(height: 8),
            Text(
              'Taux de présence: ${_stats!.attendanceRate.toStringAsFixed(1)}%',
              style: TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.bold,
                color: _stats!.attendanceRate >= 90
                    ? Colors.green
                    : _stats!.attendanceRate >= 75
                        ? Colors.orange
                        : Colors.red,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildStatItem(String label, String value, Color color, IconData icon) {
    return Column(
      children: [
        Icon(icon, color: color, size: 32),
        const SizedBox(height: 4),
        Text(
          value,
          style: TextStyle(
            fontSize: 20,
            fontWeight: FontWeight.bold,
            color: color,
          ),
        ),
        Text(
          label,
          style: TextStyle(fontSize: 12, color: Colors.grey[600]),
        ),
      ],
    );
  }

  Widget _buildChart() {
    return Card(
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Répartition',
              style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 16),
            SizedBox(
              height: 200,
              child: PieChart(
                PieChartData(
                  sections: [
                    PieChartSectionData(
                      value: _stats!.presentDays.toDouble(),
                      title: '${_stats!.presentDays}',
                      color: Colors.green,
                      radius: 60,
                      titleStyle: const TextStyle(
                        color: Colors.white,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    PieChartSectionData(
                      value: _stats!.absentDays.toDouble(),
                      title: '${_stats!.absentDays}',
                      color: Colors.red,
                      radius: 60,
                      titleStyle: const TextStyle(
                        color: Colors.white,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    PieChartSectionData(
                      value: _stats!.lateDays.toDouble(),
                      title: '${_stats!.lateDays}',
                      color: Colors.orange,
                      radius: 60,
                      titleStyle: const TextStyle(
                        color: Colors.white,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    PieChartSectionData(
                      value: _stats!.excusedDays.toDouble(),
                      title: '${_stats!.excusedDays}',
                      color: Colors.blue,
                      radius: 60,
                      titleStyle: const TextStyle(
                        color: Colors.white,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ],
                  sectionsSpace: 2,
                  centerSpaceRadius: 40,
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildRecordsList() {
    if (_records.isEmpty) {
      return const Center(
        child: Padding(
          padding: EdgeInsets.all(32),
          child: Text('Aucun enregistrement pour ce mois'),
        ),
      );
    }

    return Padding(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Historique',
            style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 8),
          ListView.builder(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            itemCount: _records.length,
            itemBuilder: (context, index) {
              return _buildRecordTile(_records[index]);
            },
          ),
        ],
      ),
    );
  }

  Widget _buildRecordTile(AttendanceRecord record) {
    final statusColor = _getStatusColor(record.status);
    final statusLabel = _getStatusLabel(record.status);
    final statusIcon = _getStatusIcon(record.status);

    return Card(
      margin: const EdgeInsets.only(bottom: 8),
      child: ListTile(
        leading: CircleAvatar(
          backgroundColor: statusColor.withOpacity(0.2),
          child: Icon(statusIcon, color: statusColor),
        ),
        title: Row(
          children: [
            Expanded(
              child: Text(DateFormat('EEEE dd MMMM yyyy', 'fr').format(record.date)),
            ),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
              decoration: BoxDecoration(
                color: statusColor.withOpacity(0.2),
                borderRadius: BorderRadius.circular(12),
              ),
              child: Text(
                statusLabel,
                style: TextStyle(
                  color: statusColor,
                  fontWeight: FontWeight.bold,
                  fontSize: 12,
                ),
              ),
            ),
          ],
        ),
        subtitle: record.reason != null
            ? Padding(
                padding: const EdgeInsets.only(top: 4),
                child: Text(record.reason!),
              )
            : null,
        trailing: record.isAbsent && !record.hasJustification
            ? IconButton(
                icon: const Icon(Icons.upload_file, color: Colors.blue),
                onPressed: () => _uploadJustification(record),
                tooltip: 'Ajouter justificatif',
              )
            : record.hasJustification
                ? const Icon(Icons.check_circle, color: Colors.green)
                : null,
      ),
    );
  }

  Color _getStatusColor(String status) {
    switch (status) {
      case 'present':
        return Colors.green;
      case 'absent':
        return Colors.red;
      case 'late':
        return Colors.orange;
      case 'excused':
        return Colors.blue;
      default:
        return Colors.grey;
    }
  }

  String _getStatusLabel(String status) {
    switch (status) {
      case 'present':
        return 'Présent';
      case 'absent':
        return 'Absent';
      case 'late':
        return 'Retard';
      case 'excused':
        return 'Excusé';
      default:
        return status;
    }
  }

  IconData _getStatusIcon(String status) {
    switch (status) {
      case 'present':
        return Icons.check_circle;
      case 'absent':
        return Icons.cancel;
      case 'late':
        return Icons.schedule;
      case 'excused':
        return Icons.event_note;
      default:
        return Icons.help;
    }
  }

  Future<void> _selectMonth() async {
    final DateTime? picked = await showDatePicker(
      context: context,
      initialDate: DateTime.parse('$_selectedMonth-01'),
      firstDate: DateTime(2020),
      lastDate: DateTime.now(),
      locale: const Locale('fr'),
      builder: (context, child) {
        return Theme(
          data: Theme.of(context).copyWith(
            colorScheme: ColorScheme.light(
              primary: Theme.of(context).primaryColor,
            ),
          ),
          child: child!,
        );
      },
    );

    if (picked != null) {
      setState(() {
        _selectedMonth = DateFormat('yyyy-MM').format(picked);
      });
      _loadAttendance();
    }
  }

  Future<void> _uploadJustification(AttendanceRecord record) async {
    // Show dialog to upload justification
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Justificatif d\'absence'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text('Date: ${DateFormat('dd/MM/yyyy').format(record.date)}'),
            const SizedBox(height: 16),
            ElevatedButton.icon(
              onPressed: () {
                // Implement file picker
                Navigator.pop(context);
                ScaffoldMessenger.of(context).showSnackBar(
                  const SnackBar(
                    content: Text('Justificatif téléchargé avec succès'),
                  ),
                );
              },
              icon: const Icon(Icons.upload),
              label: const Text('Choisir un fichier'),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Annuler'),
          ),
        ],
      ),
    );
  }
}
