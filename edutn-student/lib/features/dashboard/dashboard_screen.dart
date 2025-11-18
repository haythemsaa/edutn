import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:fl_chart/fl_chart.dart';
import '../../core/api/api_client.dart';

class DashboardScreen extends StatefulWidget {
  const DashboardScreen({Key? key}) : super(key: key);

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  final ApiClient _apiClient = ApiClient();
  StudentDashboardData? _data;
  bool _isLoading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadDashboard();
  }

  Future<void> _loadDashboard() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      final data = await _apiClient.getDashboard();
      setState(() {
        _data = StudentDashboardData.fromJson(data);
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
        title: const Text('Tableau de bord'),
        actions: [
          IconButton(
            icon: const Icon(Icons.notifications),
            onPressed: () {
              // Navigate to notifications
            },
          ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? _buildErrorView()
              : RefreshIndicator(
                  onRefresh: _loadDashboard,
                  child: SingleChildScrollView(
                    physics: const AlwaysScrollableScrollPhysics(),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        _buildWelcomeCard(),
                        _buildAcademicSummary(),
                        _buildProgressChart(),
                        _buildTodaySchedule(),
                        _buildPendingAssignments(),
                        _buildUpcomingEvents(),
                        _buildQuickActions(),
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
            onPressed: _loadDashboard,
            child: const Text('Réessayer'),
          ),
        ],
      ),
    );
  }

  Widget _buildWelcomeCard() {
    return Container(
      width: double.infinity,
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [Colors.indigo.shade700, Colors.indigo.shade500],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
      ),
      padding: const EdgeInsets.all(24),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Bonjour, ${_data!.studentName}',
            style: const TextStyle(
              color: Colors.white,
              fontSize: 24,
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            _data!.className,
            style: const TextStyle(
              color: Colors.white70,
              fontSize: 16,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            DateFormat('EEEE dd MMMM yyyy', 'fr').format(DateTime.now()),
            style: const TextStyle(
              color: Colors.white70,
              fontSize: 14,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildAcademicSummary() {
    return Padding(
      padding: const EdgeInsets.all(16),
      child: Row(
        children: [
          Expanded(
            child: Card(
              child: Container(
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                  gradient: LinearGradient(
                    colors: [Colors.blue.shade400, Colors.blue.shade600],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  ),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Column(
                  children: [
                    const Text(
                      'Moyenne générale',
                      style: TextStyle(
                        color: Colors.white70,
                        fontSize: 14,
                      ),
                    ),
                    const SizedBox(height: 8),
                    Text(
                      _data!.overallAverage.toStringAsFixed(2),
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 36,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const Text(
                      '/20',
                      style: TextStyle(
                        color: Colors.white70,
                        fontSize: 14,
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              children: [
                Card(
                  child: Padding(
                    padding: const EdgeInsets.all(16),
                    child: Row(
                      children: [
                        Icon(Icons.leaderboard, color: Colors.orange, size: 24),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text(
                                'Rang',
                                style: TextStyle(fontSize: 12),
                              ),
                              Text(
                                _data!.rank != null
                                    ? '${_data!.rank}/${_data!.totalStudents}'
                                    : '-',
                                style: const TextStyle(
                                  fontSize: 18,
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
                const SizedBox(height: 8),
                Card(
                  child: Padding(
                    padding: const EdgeInsets.all(16),
                    child: Row(
                      children: [
                        Icon(
                          Icons.event_available,
                          color: _data!.attendanceRate >= 90
                              ? Colors.green
                              : Colors.orange,
                          size: 24,
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text(
                                'Présence',
                                style: TextStyle(fontSize: 12),
                              ),
                              Text(
                                '${_data!.attendanceRate.toStringAsFixed(0)}%',
                                style: TextStyle(
                                  fontSize: 18,
                                  fontWeight: FontWeight.bold,
                                  color: _data!.attendanceRate >= 90
                                      ? Colors.green
                                      : Colors.orange,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildProgressChart() {
    if (_data!.termAverages.isEmpty) {
      return const SizedBox.shrink();
    }

    return Card(
      margin: const EdgeInsets.all(16),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'Évolution de la moyenne',
              style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
            ),
            const SizedBox(height: 16),
            SizedBox(
              height: 150,
              child: LineChart(
                LineChartData(
                  gridData: FlGridData(show: true),
                  titlesData: FlTitlesData(
                    leftTitles: AxisTitles(
                      sideTitles: SideTitles(
                        showTitles: true,
                        reservedSize: 30,
                        getTitlesWidget: (value, meta) {
                          return Text(value.toInt().toString());
                        },
                      ),
                    ),
                    bottomTitles: AxisTitles(
                      sideTitles: SideTitles(
                        showTitles: true,
                        getTitlesWidget: (value, meta) {
                          if (value.toInt() >= _data!.termAverages.length) {
                            return const Text('');
                          }
                          return Text('T${value.toInt() + 1}');
                        },
                      ),
                    ),
                    topTitles: AxisTitles(sideTitles: SideTitles(showTitles: false)),
                    rightTitles: AxisTitles(sideTitles: SideTitles(showTitles: false)),
                  ),
                  borderData: FlBorderData(show: false),
                  minY: 0,
                  maxY: 20,
                  lineBarsData: [
                    LineChartBarData(
                      spots: _data!.termAverages.asMap().entries.map((entry) {
                        return FlSpot(entry.key.toDouble(), entry.value);
                      }).toList(),
                      isCurved: true,
                      color: Colors.blue,
                      barWidth: 3,
                      dotData: FlDotData(show: true),
                      belowBarData: BarAreaData(
                        show: true,
                        color: Colors.blue.withOpacity(0.3),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildTodaySchedule() {
    if (_data!.todayClasses.isEmpty) {
      return const SizedBox.shrink();
    }

    return Padding(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text(
                'Emploi du temps aujourd\'hui',
                style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
              ),
              TextButton(
                onPressed: () {
                  // Navigate to full timetable
                },
                child: const Text('Tout voir'),
              ),
            ],
          ),
          const SizedBox(height: 12),
          SizedBox(
            height: 120,
            child: ListView.builder(
              scrollDirection: Axis.horizontal,
              itemCount: _data!.todayClasses.length,
              itemBuilder: (context, index) {
                final classItem = _data!.todayClasses[index];
                final isCurrentClass = classItem.isCurrentClass;

                return Container(
                  width: 200,
                  margin: const EdgeInsets.only(right: 12),
                  child: Card(
                    color: isCurrentClass ? Colors.green.shade50 : null,
                    elevation: isCurrentClass ? 4 : 1,
                    child: Padding(
                      padding: const EdgeInsets.all(12),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              Icon(
                                Icons.circle,
                                size: 8,
                                color: isCurrentClass ? Colors.green : Colors.grey,
                              ),
                              const SizedBox(width: 8),
                              Text(
                                '${classItem.startTime} - ${classItem.endTime}',
                                style: TextStyle(
                                  fontSize: 12,
                                  color: isCurrentClass ? Colors.green : Colors.grey[600],
                                  fontWeight: isCurrentClass
                                      ? FontWeight.bold
                                      : FontWeight.normal,
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 8),
                          Text(
                            classItem.subjectName,
                            style: const TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.bold,
                            ),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                          const SizedBox(height: 4),
                          Text(
                            classItem.teacherName,
                            style: TextStyle(
                              fontSize: 12,
                              color: Colors.grey[600],
                            ),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                          const SizedBox(height: 4),
                          Text(
                            classItem.room,
                            style: TextStyle(
                              fontSize: 12,
                              color: Colors.grey[600],
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                );
              },
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildPendingAssignments() {
    if (_data!.pendingAssignmentsList.isEmpty) {
      return const SizedBox.shrink();
    }

    return Padding(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Row(
                children: [
                  const Text(
                    'Devoirs à faire',
                    style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(width: 8),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                    decoration: BoxDecoration(
                      color: Colors.red.shade100,
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Text(
                      '${_data!.pendingAssignmentsList.length}',
                      style: TextStyle(
                        color: Colors.red.shade700,
                        fontWeight: FontWeight.bold,
                        fontSize: 12,
                      ),
                    ),
                  ),
                ],
              ),
              TextButton(
                onPressed: () {
                  // Navigate to all assignments
                },
                child: const Text('Tout voir'),
              ),
            ],
          ),
          const SizedBox(height: 12),
          ..._data!.pendingAssignmentsList.take(3).map((assignment) {
            final daysLeft = assignment.dueDate.difference(DateTime.now()).inDays;
            final isUrgent = daysLeft <= 2;

            return Card(
              margin: const EdgeInsets.only(bottom: 8),
              color: isUrgent ? Colors.red.shade50 : null,
              child: ListTile(
                leading: Container(
                  width: 40,
                  height: 40,
                  decoration: BoxDecoration(
                    color: isUrgent
                        ? Colors.red.shade100
                        : Colors.blue.shade100,
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Icon(
                    Icons.assignment,
                    color: isUrgent ? Colors.red : Colors.blue,
                  ),
                ),
                title: Text(
                  assignment.title,
                  style: const TextStyle(fontWeight: FontWeight.w500),
                ),
                subtitle: Text(
                  '${assignment.subjectName} - Échéance: ${DateFormat('dd/MM/yyyy').format(assignment.dueDate)}',
                ),
                trailing: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                    color: isUrgent
                        ? Colors.red.shade100
                        : Colors.orange.shade100,
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(
                    daysLeft == 0
                        ? 'Aujourd\'hui'
                        : daysLeft == 1
                            ? 'Demain'
                            : '$daysLeft jours',
                    style: TextStyle(
                      color: isUrgent ? Colors.red : Colors.orange,
                      fontWeight: FontWeight.bold,
                      fontSize: 12,
                    ),
                  ),
                ),
                onTap: () {
                  // Navigate to assignment details
                },
              ),
            );
          }).toList(),
        ],
      ),
    );
  }

  Widget _buildUpcomingEvents() {
    if (_data!.upcomingEvents.isEmpty) {
      return const SizedBox.shrink();
    }

    return Padding(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Événements à venir',
            style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 12),
          ..._data!.upcomingEvents.take(2).map((event) {
            return Card(
              margin: const EdgeInsets.only(bottom: 8),
              child: ListTile(
                leading: CircleAvatar(
                  backgroundColor: _getEventColor(event.type).withOpacity(0.2),
                  child: Icon(
                    _getEventIcon(event.type),
                    color: _getEventColor(event.type),
                  ),
                ),
                title: Text(event.title),
                subtitle: Text(
                  DateFormat('dd/MM/yyyy à HH:mm').format(event.date),
                ),
                trailing: const Icon(Icons.chevron_right),
                onTap: () {
                  // Navigate to event details
                },
              ),
            );
          }).toList(),
        ],
      ),
    );
  }

  Color _getEventColor(String type) {
    switch (type) {
      case 'exam':
        return Colors.red;
      case 'test':
        return Colors.orange;
      case 'activity':
        return Colors.green;
      case 'holiday':
        return Colors.blue;
      default:
        return Colors.grey;
    }
  }

  IconData _getEventIcon(String type) {
    switch (type) {
      case 'exam':
        return Icons.edit_note;
      case 'test':
        return Icons.quiz;
      case 'activity':
        return Icons.sports_soccer;
      case 'holiday':
        return Icons.celebration;
      default:
        return Icons.event;
    }
  }

  Widget _buildQuickActions() {
    return Padding(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Actions rapides',
            style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 12),
          GridView.count(
            crossAxisCount: 3,
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            mainAxisSpacing: 12,
            crossAxisSpacing: 12,
            children: [
              _buildActionButton(
                'Devoirs',
                Icons.assignment,
                Colors.blue,
                () {
                  // Navigate to assignments
                },
              ),
              _buildActionButton(
                'Notes',
                Icons.grade,
                Colors.green,
                () {
                  // Navigate to grades
                },
              ),
              _buildActionButton(
                'Emploi',
                Icons.calendar_today,
                Colors.orange,
                () {
                  // Navigate to timetable
                },
              ),
              _buildActionButton(
                'Messages',
                Icons.message,
                Colors.purple,
                () {
                  // Navigate to messages
                },
              ),
              _buildActionButton(
                'Bulletins',
                Icons.description,
                Colors.teal,
                () {
                  // Navigate to report cards
                },
              ),
              _buildActionButton(
                'Documents',
                Icons.folder,
                Colors.indigo,
                () {
                  // Navigate to documents
                },
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildActionButton(String label, IconData icon, Color color, VoidCallback onTap) {
    return Card(
      elevation: 2,
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(12),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(icon, size: 36, color: color),
            const SizedBox(height: 8),
            Text(
              label,
              textAlign: TextAlign.center,
              style: const TextStyle(fontSize: 12),
            ),
          ],
        ),
      ),
    );
  }
}

class StudentDashboardData {
  final String studentName;
  final String className;
  final double overallAverage;
  final int? rank;
  final int? totalStudents;
  final double attendanceRate;
  final List<double> termAverages;
  final List<TodayClass> todayClasses;
  final List<PendingAssignment> pendingAssignmentsList;
  final List<UpcomingEvent> upcomingEvents;

  StudentDashboardData({
    required this.studentName,
    required this.className,
    required this.overallAverage,
    this.rank,
    this.totalStudents,
    required this.attendanceRate,
    required this.termAverages,
    required this.todayClasses,
    required this.pendingAssignmentsList,
    required this.upcomingEvents,
  });

  factory StudentDashboardData.fromJson(Map<String, dynamic> json) {
    return StudentDashboardData(
      studentName: json['student_name'] ?? '',
      className: json['class_name'] ?? '',
      overallAverage: (json['overall_average'] ?? 0).toDouble(),
      rank: json['rank'],
      totalStudents: json['total_students'],
      attendanceRate: (json['attendance_rate'] ?? 0).toDouble(),
      termAverages: (json['term_averages'] as List? ?? [])
          .map((e) => (e as num).toDouble())
          .toList(),
      todayClasses: (json['today_classes'] as List? ?? [])
          .map((c) => TodayClass.fromJson(c))
          .toList(),
      pendingAssignmentsList: (json['pending_assignments'] as List? ?? [])
          .map((a) => PendingAssignment.fromJson(a))
          .toList(),
      upcomingEvents: (json['upcoming_events'] as List? ?? [])
          .map((e) => UpcomingEvent.fromJson(e))
          .toList(),
    );
  }
}

class TodayClass {
  final int id;
  final String subjectName;
  final String teacherName;
  final String room;
  final String startTime;
  final String endTime;
  final bool isCurrentClass;

  TodayClass({
    required this.id,
    required this.subjectName,
    required this.teacherName,
    required this.room,
    required this.startTime,
    required this.endTime,
    required this.isCurrentClass,
  });

  factory TodayClass.fromJson(Map<String, dynamic> json) {
    return TodayClass(
      id: json['id'],
      subjectName: json['subject_name'] ?? '',
      teacherName: json['teacher_name'] ?? '',
      room: json['room'] ?? '',
      startTime: json['start_time'] ?? '',
      endTime: json['end_time'] ?? '',
      isCurrentClass: json['is_current_class'] ?? false,
    );
  }
}

class PendingAssignment {
  final int id;
  final String title;
  final String subjectName;
  final DateTime dueDate;

  PendingAssignment({
    required this.id,
    required this.title,
    required this.subjectName,
    required this.dueDate,
  });

  factory PendingAssignment.fromJson(Map<String, dynamic> json) {
    return PendingAssignment(
      id: json['id'],
      title: json['title'] ?? '',
      subjectName: json['subject_name'] ?? '',
      dueDate: DateTime.parse(json['due_date']),
    );
  }
}

class UpcomingEvent {
  final int id;
  final String title;
  final String type;
  final DateTime date;

  UpcomingEvent({
    required this.id,
    required this.title,
    required this.type,
    required this.date,
  });

  factory UpcomingEvent.fromJson(Map<String, dynamic> json) {
    return UpcomingEvent(
      id: json['id'],
      title: json['title'] ?? '',
      type: json['type'] ?? '',
      date: DateTime.parse(json['date']),
    );
  }
}
