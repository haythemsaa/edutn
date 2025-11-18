import 'package:flutter/material.dart';
import '../../core/api/api_client.dart';
import 'timetable_model.dart';

class TimetableScreen extends StatefulWidget {
  const TimetableScreen({Key? key}) : super(key: key);

  @override
  State<TimetableScreen> createState() => _TimetableScreenState();
}

class _TimetableScreenState extends State<TimetableScreen> {
  final ApiClient _apiClient = ApiClient();
  List<TimetableEntry> _entries = [];
  bool _isLoading = true;
  String? _error;
  int _selectedDay = DateTime.now().weekday;

  @override
  void initState() {
    super.initState();
    _loadTimetable();
  }

  Future<void> _loadTimetable() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      final data = await _apiClient.getTimetable();
      setState(() {
        _entries = data.map((json) => TimetableEntry.fromJson(json)).toList();
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _error = e.toString();
        _isLoading = false;
      });
    }
  }

  List<TimetableEntry> get _selectedDayEntries {
    return _entries
        .where((e) => e.dayOfWeek == _selectedDay)
        .toList()
      ..sort((a, b) => a.startTime.compareTo(b.startTime));
  }

  Map<int, List<TimetableEntry>> get _entriesByDay {
    final map = <int, List<TimetableEntry>>{};
    for (var entry in _entries) {
      if (!map.containsKey(entry.dayOfWeek)) {
        map[entry.dayOfWeek] = [];
      }
      map[entry.dayOfWeek]!.add(entry);
    }
    // Sort entries within each day
    for (var dayEntries in map.values) {
      dayEntries.sort((a, b) => a.startTime.compareTo(b.startTime));
    }
    return map;
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Mon Emploi du Temps'),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadTimetable,
          ),
          IconButton(
            icon: const Icon(Icons.calendar_view_week),
            onPressed: _showWeekView,
          ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? _buildErrorView()
              : Column(
                  children: [
                    _buildDaySelector(),
                    Expanded(child: _buildDaySchedule()),
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
            onPressed: _loadTimetable,
            child: const Text('Réessayer'),
          ),
        ],
      ),
    );
  }

  Widget _buildDaySelector() {
    final days = [
      {'num': 1, 'short': 'Lun', 'full': 'Lundi'},
      {'num': 2, 'short': 'Mar', 'full': 'Mardi'},
      {'num': 3, 'short': 'Mer', 'full': 'Mercredi'},
      {'num': 4, 'short': 'Jeu', 'full': 'Jeudi'},
      {'num': 5, 'short': 'Ven', 'full': 'Vendredi'},
      {'num': 6, 'short': 'Sam', 'full': 'Samedi'},
    ];

    return Container(
      height: 80,
      color: Colors.grey[100],
      child: ListView.builder(
        scrollDirection: Axis.horizontal,
        padding: const EdgeInsets.symmetric(horizontal: 8),
        itemCount: days.length,
        itemBuilder: (context, index) {
          final day = days[index];
          final dayNum = day['num'] as int;
          final isSelected = _selectedDay == dayNum;
          final isToday = DateTime.now().weekday == dayNum;
          final hasClasses = _entriesByDay[dayNum]?.isNotEmpty ?? false;

          return Padding(
            padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 10),
            child: InkWell(
              onTap: () => setState(() => _selectedDay = dayNum),
              child: Container(
                width: 70,
                decoration: BoxDecoration(
                  color: isSelected ? Theme.of(context).primaryColor : Colors.white,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(
                    color: isToday ? Colors.orange : Colors.grey[300]!,
                    width: isToday ? 2 : 1,
                  ),
                ),
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Text(
                      day['short'] as String,
                      style: TextStyle(
                        fontSize: 14,
                        fontWeight: FontWeight.bold,
                        color: isSelected ? Colors.white : Colors.grey[800],
                      ),
                    ),
                    if (hasClasses) ...[
                      const SizedBox(height: 4),
                      Container(
                        width: 6,
                        height: 6,
                        decoration: BoxDecoration(
                          color: isSelected ? Colors.white : Theme.of(context).primaryColor,
                          shape: BoxShape.circle,
                        ),
                      ),
                    ],
                    if (isToday) ...[
                      const SizedBox(height: 4),
                      Text(
                        "Aujourd'hui",
                        style: TextStyle(
                          fontSize: 10,
                          color: isSelected ? Colors.white : Colors.orange,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ],
                  ],
                ),
              ),
            ),
          );
        },
      ),
    );
  }

  Widget _buildDaySchedule() {
    final dayEntries = _selectedDayEntries;

    if (dayEntries.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.event_busy, size: 64, color: Colors.grey[400]),
            const SizedBox(height: 16),
            Text(
              'Pas de cours ce jour',
              style: TextStyle(fontSize: 18, color: Colors.grey[600]),
            ),
          ],
        ),
      );
    }

    return RefreshIndicator(
      onRefresh: _loadTimetable,
      child: ListView.builder(
        padding: const EdgeInsets.all(16),
        itemCount: dayEntries.length,
        itemBuilder: (context, index) {
          return _buildClassCard(dayEntries[index]);
        },
      ),
    );
  }

  Widget _buildClassCard(TimetableEntry entry) {
    final isCurrentClass = entry.isCurrentClass();

    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      elevation: isCurrentClass ? 4 : 1,
      child: Container(
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(8),
          border: isCurrentClass
              ? Border.all(color: Colors.green, width: 3)
              : null,
        ),
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Row(
            children: [
              // Time column
              Container(
                width: 70,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.center,
                  children: [
                    Text(
                      entry.startTime,
                      style: TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                        color: isCurrentClass ? Colors.green : Theme.of(context).primaryColor,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Container(
                      width: 2,
                      height: 20,
                      color: isCurrentClass ? Colors.green : Colors.grey[300],
                    ),
                    const SizedBox(height: 4),
                    Text(
                      entry.endTime,
                      style: TextStyle(
                        fontSize: 14,
                        color: Colors.grey[600],
                      ),
                    ),
                    const SizedBox(height: 4),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                      decoration: BoxDecoration(
                        color: Colors.grey[200],
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: Text(
                        '${entry.duration}m',
                        style: TextStyle(
                          fontSize: 11,
                          color: Colors.grey[700],
                        ),
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(width: 16),

              // Subject info
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Expanded(
                          child: Text(
                            entry.subjectName ?? 'Matière',
                            style: TextStyle(
                              fontSize: 18,
                              fontWeight: FontWeight.bold,
                              color: isCurrentClass ? Colors.green : Colors.black,
                            ),
                          ),
                        ),
                        if (isCurrentClass)
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                            decoration: BoxDecoration(
                              color: Colors.green,
                              borderRadius: BorderRadius.circular(12),
                            ),
                            child: const Text(
                              'EN COURS',
                              style: TextStyle(
                                fontSize: 11,
                                color: Colors.white,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          ),
                      ],
                    ),
                    const SizedBox(height: 8),
                    Row(
                      children: [
                        Icon(Icons.person, size: 16, color: Colors.grey[600]),
                        const SizedBox(width: 4),
                        Expanded(
                          child: Text(
                            entry.teacherName ?? 'Professeur',
                            style: TextStyle(
                              fontSize: 14,
                              color: Colors.grey[700],
                            ),
                          ),
                        ),
                      ],
                    ),
                    if (entry.classroomName != null) ...[
                      const SizedBox(height: 4),
                      Row(
                        children: [
                          Icon(Icons.room, size: 16, color: Colors.grey[600]),
                          const SizedBox(width: 4),
                          Text(
                            entry.classroomName!,
                            style: TextStyle(
                              fontSize: 14,
                              color: Colors.grey[700],
                            ),
                          ),
                        ],
                      ),
                    ],
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  void _showWeekView() {
    showDialog(
      context: context,
      builder: (context) => Dialog(
        child: Container(
          padding: const EdgeInsets.all(16),
          constraints: const BoxConstraints(maxWidth: 600, maxHeight: 500),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text(
                    'Vue hebdomadaire',
                    style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold),
                  ),
                  IconButton(
                    icon: const Icon(Icons.close),
                    onPressed: () => Navigator.pop(context),
                  ),
                ],
              ),
              const SizedBox(height: 16),
              Expanded(
                child: ListView.builder(
                  itemCount: 6, // Monday to Saturday
                  itemBuilder: (context, index) {
                    final dayNum = index + 1;
                    final dayEntries = _entriesByDay[dayNum] ?? [];
                    final dayName = TimetableEntry(
                      id: 0,
                      classSectionId: 0,
                      subjectId: 0,
                      teacherId: 0,
                      dayOfWeek: dayNum,
                      startTime: '00:00',
                      endTime: '00:00',
                      createdAt: DateTime.now(),
                      updatedAt: DateTime.now(),
                    ).dayName;

                    return Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          dayName,
                          style: const TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        const SizedBox(height: 8),
                        if (dayEntries.isEmpty)
                          Padding(
                            padding: const EdgeInsets.only(left: 16, bottom: 8),
                            child: Text(
                              'Pas de cours',
                              style: TextStyle(color: Colors.grey[600]),
                            ),
                          )
                        else
                          ...dayEntries.map((entry) => Padding(
                                padding: const EdgeInsets.only(left: 16, bottom: 4),
                                child: Text(
                                  '${entry.timeRange} - ${entry.subjectName}',
                                  style: const TextStyle(fontSize: 14),
                                ),
                              )),
                        const Divider(),
                      ],
                    );
                  },
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
