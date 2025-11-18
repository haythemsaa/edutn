import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import '../../core/api/api_client.dart';
import 'assignment_model.dart';
import 'assignment_detail_screen.dart';

class AssignmentsScreen extends StatefulWidget {
  const AssignmentsScreen({Key? key}) : super(key: key);

  @override
  State<AssignmentsScreen> createState() => _AssignmentsScreenState();
}

class _AssignmentsScreenState extends State<AssignmentsScreen> with SingleTickerProviderStateMixin {
  final ApiClient _apiClient = ApiClient();
  List<Assignment> _assignments = [];
  bool _isLoading = true;
  String? _error;
  late TabController _tabController;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 4, vsync: this);
    _loadAssignments();
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  Future<void> _loadAssignments() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      final data = await _apiClient.getAssignments();
      setState(() {
        _assignments = data.map((json) => Assignment.fromJson(json)).toList();
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _error = e.toString();
        _isLoading = false;
      });
    }
  }

  List<Assignment> get _pendingAssignments {
    return _assignments
        .where((a) => !a.isSubmitted && a.status == 'active')
        .toList()
      ..sort((a, b) => a.dueDate.compareTo(b.dueDate));
  }

  List<Assignment> get _submittedAssignments {
    return _assignments
        .where((a) => a.isSubmitted && !a.isGraded)
        .toList()
      ..sort((a, b) => b.createdAt.compareTo(a.createdAt));
  }

  List<Assignment> get _gradedAssignments {
    return _assignments
        .where((a) => a.isGraded)
        .toList()
      ..sort((a, b) => b.createdAt.compareTo(a.createdAt));
  }

  List<Assignment> get _overdueAssignments {
    return _assignments
        .where((a) => a.isOverdue)
        .toList()
      ..sort((a, b) => b.dueDate.compareTo(a.dueDate));
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Mes Devoirs'),
        bottom: TabBar(
          controller: _tabController,
          isScrollable: true,
          tabs: [
            Tab(
              child: Row(
                children: [
                  const Text('À faire'),
                  if (_pendingAssignments.isNotEmpty) ...[
                    const SizedBox(width: 8),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                      decoration: BoxDecoration(
                        color: Colors.red,
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: Text(
                        _pendingAssignments.length.toString(),
                        style: const TextStyle(fontSize: 12, color: Colors.white),
                      ),
                    ),
                  ],
                ],
              ),
            ),
            Tab(
              child: Row(
                children: [
                  const Text('Soumis'),
                  if (_submittedAssignments.isNotEmpty) ...[
                    const SizedBox(width: 8),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                      decoration: BoxDecoration(
                        color: Colors.orange,
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: Text(
                        _submittedAssignments.length.toString(),
                        style: const TextStyle(fontSize: 12, color: Colors.white),
                      ),
                    ),
                  ],
                ],
              ),
            ),
            Tab(
              child: Row(
                children: [
                  const Text('Notés'),
                  if (_gradedAssignments.isNotEmpty) ...[
                    const SizedBox(width: 8),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                      decoration: BoxDecoration(
                        color: Colors.green,
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: Text(
                        _gradedAssignments.length.toString(),
                        style: const TextStyle(fontSize: 12, color: Colors.white),
                      ),
                    ),
                  ],
                ],
              ),
            ),
            Tab(
              child: Row(
                children: [
                  const Text('En retard'),
                  if (_overdueAssignments.isNotEmpty) ...[
                    const SizedBox(width: 8),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                      decoration: BoxDecoration(
                        color: Colors.red[700],
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: Text(
                        _overdueAssignments.length.toString(),
                        style: const TextStyle(fontSize: 12, color: Colors.white),
                      ),
                    ),
                  ],
                ],
              ),
            ),
          ],
        ),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? _buildErrorView()
              : TabBarView(
                  controller: _tabController,
                  children: [
                    _buildAssignmentList(_pendingAssignments, 'Aucun devoir à faire'),
                    _buildAssignmentList(_submittedAssignments, 'Aucun devoir en attente de correction'),
                    _buildAssignmentList(_gradedAssignments, 'Aucun devoir noté'),
                    _buildAssignmentList(_overdueAssignments, 'Aucun devoir en retard'),
                  ],
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
            onPressed: _loadAssignments,
            child: const Text('Réessayer'),
          ),
        ],
      ),
    );
  }

  Widget _buildAssignmentList(List<Assignment> assignments, String emptyMessage) {
    if (assignments.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.assignment_outlined, size: 64, color: Colors.grey[400]),
            const SizedBox(height: 16),
            Text(
              emptyMessage,
              style: TextStyle(fontSize: 16, color: Colors.grey[600]),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      );
    }

    return RefreshIndicator(
      onRefresh: _loadAssignments,
      child: ListView.builder(
        padding: const EdgeInsets.all(16),
        itemCount: assignments.length,
        itemBuilder: (context, index) {
          return _buildAssignmentCard(assignments[index]);
        },
      ),
    );
  }

  Widget _buildAssignmentCard(Assignment assignment) {
    final bool isOverdue = assignment.isOverdue;
    final bool isUrgent = assignment.daysUntilDue <= 2 && !assignment.isSubmitted;

    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      child: InkWell(
        onTap: () {
          Navigator.push(
            context,
            MaterialPageRoute(
              builder: (context) => AssignmentDetailScreen(assignment: assignment),
            ),
          ).then((_) => _loadAssignments());
        },
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                    decoration: BoxDecoration(
                      color: _getTypeColor(assignment.type).withOpacity(0.1),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Text(
                      assignment.typeLabel,
                      style: TextStyle(
                        fontSize: 12,
                        color: _getTypeColor(assignment.type),
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      assignment.subjectName ?? '',
                      style: TextStyle(
                        fontSize: 12,
                        color: Colors.grey[600],
                      ),
                    ),
                  ),
                  if (assignment.maxScore != null)
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                      decoration: BoxDecoration(
                        color: Colors.blue[100],
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Text(
                        '/${assignment.maxScore}',
                        style: const TextStyle(
                          fontSize: 12,
                          color: Colors.blue,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ),
                ],
              ),
              const SizedBox(height: 12),
              Text(
                assignment.title,
                style: const TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.bold,
                ),
              ),
              if (assignment.description != null) ...[
                const SizedBox(height: 8),
                Text(
                  assignment.description!,
                  style: TextStyle(
                    fontSize: 14,
                    color: Colors.grey[600],
                  ),
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                ),
              ],
              const SizedBox(height: 12),
              Row(
                children: [
                  Icon(
                    Icons.calendar_today,
                    size: 16,
                    color: isOverdue ? Colors.red : (isUrgent ? Colors.orange : Colors.grey[600]),
                  ),
                  const SizedBox(width: 4),
                  Text(
                    'À rendre: ${DateFormat('dd/MM/yyyy HH:mm').format(assignment.dueDate)}',
                    style: TextStyle(
                      fontSize: 13,
                      color: isOverdue ? Colors.red : (isUrgent ? Colors.orange : Colors.grey[600]),
                      fontWeight: isOverdue || isUrgent ? FontWeight.bold : FontWeight.normal,
                    ),
                  ),
                  if (isUrgent && !isOverdue) ...[
                    const SizedBox(width: 8),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                      decoration: BoxDecoration(
                        color: Colors.orange,
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: Text(
                        '${assignment.daysUntilDue}j',
                        style: const TextStyle(fontSize: 10, color: Colors.white),
                      ),
                    ),
                  ],
                ],
              ),
              if (assignment.isSubmitted) ...[
                const SizedBox(height: 8),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                    color: assignment.isGraded ? Colors.green[100] : Colors.blue[100],
                    borderRadius: BorderRadius.circular(4),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(
                        assignment.isGraded ? Icons.grade : Icons.check_circle,
                        size: 16,
                        color: assignment.isGraded ? Colors.green[700] : Colors.blue[700],
                      ),
                      const SizedBox(width: 4),
                      Text(
                        assignment.isGraded
                            ? 'Note: ${assignment.mySubmission!.grade}/${assignment.mySubmission!.maxScore}'
                            : 'Soumis le ${DateFormat('dd/MM').format(assignment.mySubmission!.submittedAt)}',
                        style: TextStyle(
                          fontSize: 12,
                          color: assignment.isGraded ? Colors.green[700] : Colors.blue[700],
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
              if (isOverdue) ...[
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
                        'En retard',
                        style: TextStyle(
                          fontSize: 12,
                          color: Colors.red[700],
                          fontWeight: FontWeight.bold,
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
