class Assignment {
  final int id;
  final int schoolId;
  final int teacherId;
  final int classId;
  final int subjectId;
  final String title;
  final String? titleAr;
  final String? description;
  final String? descriptionAr;
  final String type;
  final DateTime dueDate;
  final int? maxScore;
  final String? attachmentUrl;
  final String status;
  final DateTime createdAt;
  final DateTime updatedAt;

  // Related data
  final String? teacherName;
  final String? subjectName;
  final AssignmentSubmission? mySubmission;

  Assignment({
    required this.id,
    required this.schoolId,
    required this.teacherId,
    required this.classId,
    required this.subjectId,
    required this.title,
    this.titleAr,
    this.description,
    this.descriptionAr,
    required this.type,
    required this.dueDate,
    this.maxScore,
    this.attachmentUrl,
    required this.status,
    required this.createdAt,
    required this.updatedAt,
    this.teacherName,
    this.subjectName,
    this.mySubmission,
  });

  factory Assignment.fromJson(Map<String, dynamic> json) {
    return Assignment(
      id: json['id'],
      schoolId: json['school_id'],
      teacherId: json['teacher_id'],
      classId: json['class_id'],
      subjectId: json['subject_id'],
      title: json['title'],
      titleAr: json['title_ar'],
      description: json['description'],
      descriptionAr: json['description_ar'],
      type: json['type'],
      dueDate: DateTime.parse(json['due_date']),
      maxScore: json['max_score'],
      attachmentUrl: json['attachment_url'],
      status: json['status'] ?? 'active',
      createdAt: DateTime.parse(json['created_at']),
      updatedAt: DateTime.parse(json['updated_at']),
      teacherName: json['teacher_name'],
      subjectName: json['subject_name'],
      mySubmission: json['my_submission'] != null
          ? AssignmentSubmission.fromJson(json['my_submission'])
          : null,
    );
  }

  bool get isOverdue => DateTime.now().isAfter(dueDate) && mySubmission == null;
  bool get isSubmitted => mySubmission != null;
  bool get isGraded => mySubmission?.grade != null;

  String get typeLabel {
    switch (type) {
      case 'homework':
        return 'Devoir';
      case 'project':
        return 'Projet';
      case 'exam':
        return 'Examen';
      case 'quiz':
        return 'Quiz';
      case 'presentation':
        return 'Présentation';
      default:
        return 'Autre';
    }
  }

  int get daysUntilDue {
    return dueDate.difference(DateTime.now()).inDays;
  }
}

class AssignmentSubmission {
  final int id;
  final int assignmentId;
  final int studentId;
  final String? content;
  final String? attachmentUrl;
  final DateTime submittedAt;
  final int? grade;
  final int? maxScore;
  final String? feedback;
  final String? feedbackAr;
  final String status;

  AssignmentSubmission({
    required this.id,
    required this.assignmentId,
    required this.studentId,
    this.content,
    this.attachmentUrl,
    required this.submittedAt,
    this.grade,
    this.maxScore,
    this.feedback,
    this.feedbackAr,
    required this.status,
  });

  factory AssignmentSubmission.fromJson(Map<String, dynamic> json) {
    return AssignmentSubmission(
      id: json['id'],
      assignmentId: json['assignment_id'],
      studentId: json['student_id'],
      content: json['content'],
      attachmentUrl: json['attachment_url'],
      submittedAt: DateTime.parse(json['submitted_at']),
      grade: json['grade'],
      maxScore: json['max_score'],
      feedback: json['feedback'],
      feedbackAr: json['feedback_ar'],
      status: json['status'] ?? 'submitted',
    );
  }

  String get gradePercentage {
    if (grade == null || maxScore == null || maxScore == 0) return '-';
    return '${((grade! / maxScore!) * 100).toStringAsFixed(0)}%';
  }

  bool get isGraded => grade != null;
  bool get isLate => status == 'late';
}
