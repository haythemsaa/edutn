class AttendanceRecord {
  final int id;
  final int studentId;
  final String studentName;
  final DateTime date;
  final String status; // present, absent, late, excused
  final String? reason;
  final String? justificationPath;
  final String? notes;
  final DateTime createdAt;

  AttendanceRecord({
    required this.id,
    required this.studentId,
    required this.studentName,
    required this.date,
    required this.status,
    this.reason,
    this.justificationPath,
    this.notes,
    required this.createdAt,
  });

  factory AttendanceRecord.fromJson(Map<String, dynamic> json) {
    return AttendanceRecord(
      id: json['id'],
      studentId: json['student_id'],
      studentName: json['student_name'] ?? '',
      date: DateTime.parse(json['date']),
      status: json['status'],
      reason: json['reason'],
      justificationPath: json['justification_path'],
      notes: json['notes'],
      createdAt: DateTime.parse(json['created_at']),
    );
  }

  bool get isAbsent => status == 'absent';
  bool get isLate => status == 'late';
  bool get isPresent => status == 'present';
  bool get isExcused => status == 'excused';
  bool get hasJustification => justificationPath != null;
}

class AttendanceStats {
  final int totalDays;
  final int presentDays;
  final int absentDays;
  final int lateDays;
  final int excusedDays;
  final double attendanceRate;

  AttendanceStats({
    required this.totalDays,
    required this.presentDays,
    required this.absentDays,
    required this.lateDays,
    required this.excusedDays,
    required this.attendanceRate,
  });

  factory AttendanceStats.fromJson(Map<String, dynamic> json) {
    return AttendanceStats(
      totalDays: json['total_days'] ?? 0,
      presentDays: json['present_days'] ?? 0,
      absentDays: json['absent_days'] ?? 0,
      lateDays: json['late_days'] ?? 0,
      excusedDays: json['excused_days'] ?? 0,
      attendanceRate: (json['attendance_rate'] ?? 0.0).toDouble(),
    );
  }
}
