import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../../core/api/api_client.dart';

class DashboardScreen extends StatefulWidget {
  const DashboardScreen({Key? key}) : super(key: key);

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  final ApiClient _apiClient = ApiClient();
  DashboardData? _data;
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
        _data = DashboardData.fromJson(data);
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
                        _buildChildrenCards(),
                        _buildQuickStats(),
                        _buildUpcomingEvents(),
                        _buildRecentActivity(),
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
          colors: [Colors.blue.shade700, Colors.blue.shade500],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
      ),
      padding: const EdgeInsets.all(24),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Bonjour, ${_data!.parentName}',
            style: const TextStyle(
              color: Colors.white,
              fontSize: 24,
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 8),
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

  Widget _buildChildrenCards() {
    return Padding(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Mes enfants',
            style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 12),
          SizedBox(
            height: 120,
            child: ListView.builder(
              scrollDirection: Axis.horizontal,
              itemCount: _data!.children.length,
              itemBuilder: (context, index) {
                final child = _data!.children[index];
                return _buildChildCard(child);
              },
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildChildCard(ChildSummary child) {
    return Container(
      width: 280,
      margin: const EdgeInsets.only(right: 12),
      child: Card(
        elevation: 2,
        child: InkWell(
          onTap: () {
            // Navigate to child details
          },
          borderRadius: BorderRadius.circular(12),
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    CircleAvatar(
                      backgroundColor: Colors.blue.shade100,
                      child: Text(
                        child.name[0],
                        style: TextStyle(
                          color: Colors.blue.shade700,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            child.name,
                            style: const TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          Text(
                            child.className,
                            style: TextStyle(
                              fontSize: 12,
                              color: Colors.grey[600],
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 12),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceAround,
                  children: [
                    _buildChildStat(
                      'Moyenne',
                      child.average.toStringAsFixed(1),
                      Colors.blue,
                    ),
                    _buildChildStat(
                      'Présence',
                      '${child.attendanceRate.toStringAsFixed(0)}%',
                      child.attendanceRate >= 90 ? Colors.green : Colors.orange,
                    ),
                  ],
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildChildStat(String label, String value, Color color) {
    return Column(
      children: [
        Text(
          value,
          style: TextStyle(
            fontSize: 18,
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

  Widget _buildQuickStats() {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Résumé',
            style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 12),
          Row(
            children: [
              Expanded(
                child: _buildStatCard(
                  'Devoirs',
                  _data!.pendingAssignments.toString(),
                  'à faire',
                  Icons.assignment,
                  Colors.orange,
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: _buildStatCard(
                  'Factures',
                  _data!.pendingInvoices.toString(),
                  'en attente',
                  Icons.receipt,
                  Colors.red,
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          Row(
            children: [
              Expanded(
                child: _buildStatCard(
                  'Messages',
                  _data!.unreadMessages.toString(),
                  'non lus',
                  Icons.message,
                  Colors.blue,
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: _buildStatCard(
                  'Rendez-vous',
                  _data!.upcomingAppointments.toString(),
                  'à venir',
                  Icons.event,
                  Colors.green,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildStatCard(String title, String count, String subtitle, IconData icon, Color color) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Icon(icon, color: color, size: 24),
                const Spacer(),
                Text(
                  count,
                  style: TextStyle(
                    fontSize: 28,
                    fontWeight: FontWeight.bold,
                    color: color,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 8),
            Text(
              title,
              style: const TextStyle(
                fontSize: 14,
                fontWeight: FontWeight.w500,
              ),
            ),
            Text(
              subtitle,
              style: TextStyle(
                fontSize: 12,
                color: Colors.grey[600],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildUpcomingEvents() {
    if (_data!.upcomingEventsList.isEmpty) {
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
                'Événements à venir',
                style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
              ),
              TextButton(
                onPressed: () {
                  // Navigate to all events
                },
                child: const Text('Tout voir'),
              ),
            ],
          ),
          const SizedBox(height: 12),
          ..._data!.upcomingEventsList.take(3).map((event) => _buildEventCard(event)).toList(),
        ],
      ),
    );
  }

  Widget _buildEventCard(DashboardEvent event) {
    return Card(
      margin: const EdgeInsets.only(bottom: 8),
      child: ListTile(
        leading: Container(
          width: 48,
          height: 48,
          decoration: BoxDecoration(
            color: _getEventColor(event.type).withOpacity(0.2),
            borderRadius: BorderRadius.circular(8),
          ),
          child: Icon(
            _getEventIcon(event.type),
            color: _getEventColor(event.type),
          ),
        ),
        title: Text(event.title),
        subtitle: Text(DateFormat('dd/MM/yyyy à HH:mm').format(event.date)),
        trailing: const Icon(Icons.chevron_right),
        onTap: () {
          // Navigate to event details
        },
      ),
    );
  }

  Color _getEventColor(String type) {
    switch (type) {
      case 'exam':
        return Colors.red;
      case 'meeting':
        return Colors.blue;
      case 'holiday':
        return Colors.green;
      case 'activity':
        return Colors.orange;
      default:
        return Colors.grey;
    }
  }

  IconData _getEventIcon(String type) {
    switch (type) {
      case 'exam':
        return Icons.edit_note;
      case 'meeting':
        return Icons.people;
      case 'holiday':
        return Icons.celebration;
      case 'activity':
        return Icons.sports_soccer;
      default:
        return Icons.event;
    }
  }

  Widget _buildRecentActivity() {
    if (_data!.recentActivities.isEmpty) {
      return const SizedBox.shrink();
    }

    return Padding(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Activité récente',
            style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 12),
          Card(
            child: ListView.separated(
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              itemCount: _data!.recentActivities.length > 5 ? 5 : _data!.recentActivities.length,
              separatorBuilder: (context, index) => const Divider(height: 1),
              itemBuilder: (context, index) {
                final activity = _data!.recentActivities[index];
                return ListTile(
                  leading: Icon(
                    _getActivityIcon(activity.type),
                    color: Colors.blue,
                  ),
                  title: Text(activity.title),
                  subtitle: Text(activity.description),
                  trailing: Text(
                    _formatTime(activity.timestamp),
                    style: TextStyle(fontSize: 12, color: Colors.grey[600]),
                  ),
                );
              },
            ),
          ),
        ],
      ),
    );
  }

  IconData _getActivityIcon(String type) {
    switch (type) {
      case 'grade':
        return Icons.grade;
      case 'attendance':
        return Icons.check_circle;
      case 'message':
        return Icons.message;
      case 'payment':
        return Icons.payment;
      case 'document':
        return Icons.description;
      default:
        return Icons.info;
    }
  }

  String _formatTime(DateTime timestamp) {
    final now = DateTime.now();
    final difference = now.difference(timestamp);

    if (difference.inDays > 0) {
      return 'Il y a ${difference.inDays}j';
    } else if (difference.inHours > 0) {
      return 'Il y a ${difference.inHours}h';
    } else if (difference.inMinutes > 0) {
      return 'Il y a ${difference.inMinutes}min';
    } else {
      return 'À l\'instant';
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
                'Assiduité',
                Icons.event_available,
                Colors.green,
                () {
                  // Navigate to attendance
                },
              ),
              _buildActionButton(
                'Notes',
                Icons.assessment,
                Colors.blue,
                () {
                  // Navigate to grades
                },
              ),
              _buildActionButton(
                'Paiements',
                Icons.account_balance_wallet,
                Colors.orange,
                () {
                  // Navigate to payments
                },
              ),
              _buildActionButton(
                'Messages',
                Icons.forum,
                Colors.purple,
                () {
                  // Navigate to messages
                },
              ),
              _buildActionButton(
                'Documents',
                Icons.folder,
                Colors.teal,
                () {
                  // Navigate to documents
                },
              ),
              _buildActionButton(
                'Rendez-vous',
                Icons.calendar_today,
                Colors.pink,
                () {
                  // Navigate to appointments
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

class DashboardData {
  final String parentName;
  final List<ChildSummary> children;
  final int pendingAssignments;
  final int pendingInvoices;
  final int unreadMessages;
  final int upcomingAppointments;
  final List<DashboardEvent> upcomingEventsList;
  final List<DashboardActivity> recentActivities;

  DashboardData({
    required this.parentName,
    required this.children,
    required this.pendingAssignments,
    required this.pendingInvoices,
    required this.unreadMessages,
    required this.upcomingAppointments,
    required this.upcomingEventsList,
    required this.recentActivities,
  });

  factory DashboardData.fromJson(Map<String, dynamic> json) {
    return DashboardData(
      parentName: json['parent_name'] ?? '',
      children: (json['children'] as List? ?? [])
          .map((c) => ChildSummary.fromJson(c))
          .toList(),
      pendingAssignments: json['pending_assignments'] ?? 0,
      pendingInvoices: json['pending_invoices'] ?? 0,
      unreadMessages: json['unread_messages'] ?? 0,
      upcomingAppointments: json['upcoming_appointments'] ?? 0,
      upcomingEventsList: (json['upcoming_events'] as List? ?? [])
          .map((e) => DashboardEvent.fromJson(e))
          .toList(),
      recentActivities: (json['recent_activities'] as List? ?? [])
          .map((a) => DashboardActivity.fromJson(a))
          .toList(),
    );
  }
}

class ChildSummary {
  final int id;
  final String name;
  final String className;
  final double average;
  final double attendanceRate;

  ChildSummary({
    required this.id,
    required this.name,
    required this.className,
    required this.average,
    required this.attendanceRate,
  });

  factory ChildSummary.fromJson(Map<String, dynamic> json) {
    return ChildSummary(
      id: json['id'],
      name: json['name'] ?? '',
      className: json['class_name'] ?? '',
      average: (json['average'] ?? 0).toDouble(),
      attendanceRate: (json['attendance_rate'] ?? 0).toDouble(),
    );
  }
}

class DashboardEvent {
  final int id;
  final String title;
  final String type;
  final DateTime date;

  DashboardEvent({
    required this.id,
    required this.title,
    required this.type,
    required this.date,
  });

  factory DashboardEvent.fromJson(Map<String, dynamic> json) {
    return DashboardEvent(
      id: json['id'],
      title: json['title'] ?? '',
      type: json['type'] ?? '',
      date: DateTime.parse(json['date']),
    );
  }
}

class DashboardActivity {
  final int id;
  final String type;
  final String title;
  final String description;
  final DateTime timestamp;

  DashboardActivity({
    required this.id,
    required this.type,
    required this.title,
    required this.description,
    required this.timestamp,
  });

  factory DashboardActivity.fromJson(Map<String, dynamic> json) {
    return DashboardActivity(
      id: json['id'],
      type: json['type'] ?? '',
      title: json['title'] ?? '',
      description: json['description'] ?? '',
      timestamp: DateTime.parse(json['timestamp']),
    );
  }
}
