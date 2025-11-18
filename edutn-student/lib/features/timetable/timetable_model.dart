class TimetableEntry {
  final int id;
  final int classSectionId;
  final int subjectId;
  final int teacherId;
  final int? classroomId;
  final int dayOfWeek; // 1-7 (Monday-Sunday)
  final String startTime; // HH:mm
  final String endTime; // HH:mm
  final DateTime createdAt;
  final DateTime updatedAt;

  // Related data
  final String? subjectName;
  final String? subjectNameAr;
  final String? teacherName;
  final String? classroomName;

  TimetableEntry({
    required this.id,
    required this.classSectionId,
    required this.subjectId,
    required this.teacherId,
    this.classroomId,
    required this.dayOfWeek,
    required this.startTime,
    required this.endTime,
    required this.createdAt,
    required this.updatedAt,
    this.subjectName,
    this.subjectNameAr,
    this.teacherName,
    this.classroomName,
  });

  factory TimetableEntry.fromJson(Map<String, dynamic> json) {
    return TimetableEntry(
      id: json['id'],
      classSectionId: json['class_section_id'],
      subjectId: json['subject_id'],
      teacherId: json['teacher_id'],
      classroomId: json['classroom_id'],
      dayOfWeek: json['day_of_week'],
      startTime: json['start_time'],
      endTime: json['end_time'],
      createdAt: DateTime.parse(json['created_at']),
      updatedAt: DateTime.parse(json['updated_at']),
      subjectName: json['subject_name'],
      subjectNameAr: json['subject_name_ar'],
      teacherName: json['teacher_name'],
      classroomName: json['classroom_name'],
    );
  }

  String get dayName {
    switch (dayOfWeek) {
      case 1:
        return 'Lundi';
      case 2:
        return 'Mardi';
      case 3:
        return 'Mercredi';
      case 4:
        return 'Jeudi';
      case 5:
        return 'Vendredi';
      case 6:
        return 'Samedi';
      case 7:
        return 'Dimanche';
      default:
        return 'Inconnu';
    }
  }

  String get dayNameShort {
    switch (dayOfWeek) {
      case 1:
        return 'Lun';
      case 2:
        return 'Mar';
      case 3:
        return 'Mer';
      case 4:
        return 'Jeu';
      case 5:
        return 'Ven';
      case 6:
        return 'Sam';
      case 7:
        return 'Dim';
      default:
        return '?';
    }
  }

  String get timeRange => '$startTime - $endTime';

  int get duration {
    final start = _parseTime(startTime);
    final end = _parseTime(endTime);
    return end.difference(start).inMinutes;
  }

  DateTime _parseTime(String time) {
    final parts = time.split(':');
    final now = DateTime.now();
    return DateTime(now.year, now.month, now.day, int.parse(parts[0]), int.parse(parts[1]));
  }

  bool isCurrentClass() {
    final now = DateTime.now();
    final todayDayOfWeek = now.weekday; // 1-7 (Monday-Sunday)

    if (todayDayOfWeek != dayOfWeek) return false;

    final currentTime = '${now.hour.toString().padLeft(2, '0')}:${now.minute.toString().padLeft(2, '0')}';
    return currentTime.compareTo(startTime) >= 0 && currentTime.compareTo(endTime) < 0;
  }
}
