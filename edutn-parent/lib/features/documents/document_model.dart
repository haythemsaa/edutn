class Document {
  final int id;
  final int schoolId;
  final int? studentId;
  final int? teacherId;
  final String documentType;
  final String title;
  final String? titleAr;
  final String? description;
  final String? descriptionAr;
  final String filePath;
  final String fileType;
  final int fileSize;
  final int uploadedBy;
  final bool isOfficial;
  final bool requiresSignature;
  final String? documentNumber;
  final DateTime? issueDate;
  final DateTime? expiryDate;
  final String status;
  final Map<String, dynamic>? metadata;
  final List<String>? tags;
  final int downloadCount;
  final DateTime? lastDownloadedAt;
  final DateTime createdAt;
  final DateTime updatedAt;

  Document({
    required this.id,
    required this.schoolId,
    this.studentId,
    this.teacherId,
    required this.documentType,
    required this.title,
    this.titleAr,
    this.description,
    this.descriptionAr,
    required this.filePath,
    required this.fileType,
    required this.fileSize,
    required this.uploadedBy,
    required this.isOfficial,
    required this.requiresSignature,
    this.documentNumber,
    this.issueDate,
    this.expiryDate,
    required this.status,
    this.metadata,
    this.tags,
    required this.downloadCount,
    this.lastDownloadedAt,
    required this.createdAt,
    required this.updatedAt,
  });

  factory Document.fromJson(Map<String, dynamic> json) {
    return Document(
      id: json['id'],
      schoolId: json['school_id'],
      studentId: json['student_id'],
      teacherId: json['teacher_id'],
      documentType: json['document_type'],
      title: json['title'],
      titleAr: json['title_ar'],
      description: json['description'],
      descriptionAr: json['description_ar'],
      filePath: json['file_path'],
      fileType: json['file_type'],
      fileSize: json['file_size'],
      uploadedBy: json['uploaded_by'],
      isOfficial: json['is_official'] ?? false,
      requiresSignature: json['requires_signature'] ?? false,
      documentNumber: json['document_number'],
      issueDate: json['issue_date'] != null ? DateTime.parse(json['issue_date']) : null,
      expiryDate: json['expiry_date'] != null ? DateTime.parse(json['expiry_date']) : null,
      status: json['status'] ?? 'active',
      metadata: json['metadata'],
      tags: json['tags'] != null ? List<String>.from(json['tags']) : null,
      downloadCount: json['download_count'] ?? 0,
      lastDownloadedAt: json['last_downloaded_at'] != null
          ? DateTime.parse(json['last_downloaded_at'])
          : null,
      createdAt: DateTime.parse(json['created_at']),
      updatedAt: DateTime.parse(json['updated_at']),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'school_id': schoolId,
      'student_id': studentId,
      'teacher_id': teacherId,
      'document_type': documentType,
      'title': title,
      'title_ar': titleAr,
      'description': description,
      'description_ar': descriptionAr,
      'file_path': filePath,
      'file_type': fileType,
      'file_size': fileSize,
      'uploaded_by': uploadedBy,
      'is_official': isOfficial,
      'requires_signature': requiresSignature,
      'document_number': documentNumber,
      'issue_date': issueDate?.toIso8601String(),
      'expiry_date': expiryDate?.toIso8601String(),
      'status': status,
      'metadata': metadata,
      'tags': tags,
      'download_count': downloadCount,
      'last_downloaded_at': lastDownloadedAt?.toIso8601String(),
      'created_at': createdAt.toIso8601String(),
      'updated_at': updatedAt.toIso8601String(),
    };
  }

  String get formattedFileSize {
    if (fileSize < 1024) return '$fileSize B';
    if (fileSize < 1024 * 1024) return '${(fileSize / 1024).toStringAsFixed(1)} KB';
    return '${(fileSize / (1024 * 1024)).toStringAsFixed(1)} MB';
  }

  String get fileExtension {
    return filePath.split('.').last.toUpperCase();
  }

  bool get isExpired {
    if (expiryDate == null) return false;
    return expiryDate!.isBefore(DateTime.now());
  }

  bool get isPdf => fileType.contains('pdf');
  bool get isImage => fileType.contains('image');
  bool get isWord => fileType.contains('word') || fileType.contains('msword');
  bool get isExcel => fileType.contains('excel') || fileType.contains('spreadsheet');
}
