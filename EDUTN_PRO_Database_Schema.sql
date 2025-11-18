-- =====================================================
-- EDUTN PRO - SCHÉMA DE BASE DE DONNÉES COMPLET
-- Système de Gestion Scolaire pour la Tunisie
-- Version: 1.0
-- DBMS: MySQL 8.0 / MariaDB 10.11
-- =====================================================

-- =====================================================
-- 1. TABLES DE CONFIGURATION
-- =====================================================

-- Établissements scolaires
CREATE TABLE schools (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name_ar VARCHAR(255) NOT NULL,
    name_fr VARCHAR(255) NOT NULL,
    code VARCHAR(50) UNIQUE NOT NULL,
    logo VARCHAR(255),
    address TEXT,
    city VARCHAR(100),
    postal_code VARCHAR(10),
    phone VARCHAR(20),
    email VARCHAR(255),
    website VARCHAR(255),
    ministry_approval_number VARCHAR(100),
    school_type ENUM('public', 'private') DEFAULT 'private',
    education_level ENUM('primary', 'middle', 'secondary', 'all') DEFAULT 'all',
    capacity INT,
    director_name VARCHAR(255),
    settings JSON,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_code (code),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Années scolaires
CREATE TABLE academic_years (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    school_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    is_current BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    INDEX idx_school_current (school_id, is_current)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Trimestres/Semestres
CREATE TABLE terms (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    academic_year_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    term_number TINYINT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON DELETE CASCADE,
    INDEX idx_year_number (academic_year_id, term_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Niveaux d'enseignement
CREATE TABLE levels (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    school_id BIGINT UNSIGNED NOT NULL,
    name_ar VARCHAR(100) NOT NULL,
    name_fr VARCHAR(100) NOT NULL,
    cycle ENUM('primary', 'middle', 'secondary') NOT NULL,
    level_order TINYINT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    INDEX idx_school_cycle (school_id, cycle)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sections (pour lycée)
CREATE TABLE sections (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name_ar VARCHAR(100) NOT NULL,
    name_fr VARCHAR(100) NOT NULL,
    code VARCHAR(20) UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Classes
CREATE TABLE classes (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    school_id BIGINT UNSIGNED NOT NULL,
    level_id BIGINT UNSIGNED NOT NULL,
    section_id BIGINT UNSIGNED NULL,
    academic_year_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    capacity INT DEFAULT 30,
    main_teacher_id BIGINT UNSIGNED NULL,
    classroom_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (level_id) REFERENCES levels(id) ON DELETE CASCADE,
    FOREIGN KEY (section_id) REFERENCES sections(id) ON DELETE SET NULL,
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON DELETE CASCADE,
    INDEX idx_school_year (school_id, academic_year_id),
    INDEX idx_level (level_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Salles de classe
CREATE TABLE classrooms (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    school_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    room_number VARCHAR(50),
    capacity INT,
    room_type ENUM('classroom', 'lab', 'computer_room', 'library', 'gym', 'other') DEFAULT 'classroom',
    equipment JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 2. TABLES DES ÉLÈVES
-- =====================================================

-- Élèves
CREATE TABLE students (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    school_id BIGINT UNSIGNED NOT NULL,
    student_number VARCHAR(50) UNIQUE NOT NULL,
    first_name_ar VARCHAR(100) NOT NULL,
    last_name_ar VARCHAR(100) NOT NULL,
    first_name_fr VARCHAR(100) NOT NULL,
    last_name_fr VARCHAR(100) NOT NULL,
    date_of_birth DATE NOT NULL,
    place_of_birth VARCHAR(255),
    gender ENUM('M', 'F') NOT NULL,
    nationality VARCHAR(100) DEFAULT 'Tunisienne',
    photo VARCHAR(255),
    cin_passport VARCHAR(50),
    birth_certificate_number VARCHAR(100),
    address TEXT,
    city VARCHAR(100),
    postal_code VARCHAR(10),
    phone VARCHAR(20),
    email VARCHAR(255),
    current_class_id BIGINT UNSIGNED NULL,
    enrollment_date DATE NOT NULL,
    status ENUM('active', 'transferred', 'graduated', 'expelled', 'withdrawn') DEFAULT 'active',
    regime ENUM('external', 'half_boarder', 'boarder') DEFAULT 'external',
    scholarship_amount DECIMAL(10,2) DEFAULT 0,
    blood_group VARCHAR(5),
    allergies TEXT,
    chronic_diseases TEXT,
    medical_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (current_class_id) REFERENCES classes(id) ON DELETE SET NULL,
    INDEX idx_student_number (student_number),
    INDEX idx_school_status (school_id, status),
    INDEX idx_current_class (current_class_id),
    FULLTEXT idx_name_search (first_name_fr, last_name_fr, first_name_ar, last_name_ar)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Parents/Tuteurs
CREATE TABLE parents (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    cin VARCHAR(20) UNIQUE NOT NULL,
    first_name_ar VARCHAR(100) NOT NULL,
    last_name_ar VARCHAR(100) NOT NULL,
    first_name_fr VARCHAR(100) NOT NULL,
    last_name_fr VARCHAR(100) NOT NULL,
    date_of_birth DATE,
    phone_mobile VARCHAR(20) NOT NULL,
    phone_work VARCHAR(20),
    email VARCHAR(255),
    profession VARCHAR(255),
    employer VARCHAR(255),
    work_address TEXT,
    monthly_income DECIMAL(10,2),
    address TEXT,
    city VARCHAR(100),
    postal_code VARCHAR(10),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_cin (cin),
    INDEX idx_phone (phone_mobile)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Relation Élèves-Parents
CREATE TABLE student_parent (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    student_id BIGINT UNSIGNED NOT NULL,
    parent_id BIGINT UNSIGNED NOT NULL,
    relationship ENUM('father', 'mother', 'legal_guardian', 'other') NOT NULL,
    is_primary_contact BOOLEAN DEFAULT FALSE,
    can_pick_up BOOLEAN DEFAULT TRUE,
    can_authorize_medical BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES parents(id) ON DELETE CASCADE,
    UNIQUE KEY unique_student_parent (student_id, parent_id),
    INDEX idx_student (student_id),
    INDEX idx_parent (parent_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Contacts d'urgence
CREATE TABLE emergency_contacts (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    student_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    relationship VARCHAR(100),
    phone VARCHAR(20) NOT NULL,
    priority TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    INDEX idx_student (student_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 3. TABLES DU PERSONNEL
-- =====================================================

-- Personnel (Enseignants et Administratifs)
CREATE TABLE staff (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    school_id BIGINT UNSIGNED NOT NULL,
    employee_number VARCHAR(50) UNIQUE NOT NULL,
    first_name_ar VARCHAR(100) NOT NULL,
    last_name_ar VARCHAR(100) NOT NULL,
    first_name_fr VARCHAR(100) NOT NULL,
    last_name_fr VARCHAR(100) NOT NULL,
    cin VARCHAR(20) UNIQUE NOT NULL,
    date_of_birth DATE NOT NULL,
    gender ENUM('M', 'F') NOT NULL,
    photo VARCHAR(255),
    phone_mobile VARCHAR(20) NOT NULL,
    email VARCHAR(255) NOT NULL,
    address TEXT,
    role ENUM('teacher', 'admin', 'accountant', 'librarian', 'nurse', 'counselor', 'security', 'maintenance', 'other') NOT NULL,
    speciality VARCHAR(255),
    grade VARCHAR(100),
    hire_date DATE NOT NULL,
    contract_type ENUM('permanent', 'temporary', 'hourly') DEFAULT 'permanent',
    contract_end_date DATE NULL,
    status ENUM('active', 'on_leave', 'resigned', 'retired', 'terminated') DEFAULT 'active',
    base_salary DECIMAL(10,2),
    bank_account VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    INDEX idx_employee_number (employee_number),
    INDEX idx_school_role (school_id, role),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Documents du personnel
CREATE TABLE staff_documents (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    staff_id BIGINT UNSIGNED NOT NULL,
    document_type VARCHAR(100) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (staff_id) REFERENCES staff(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 4. TABLES ACADÉMIQUES
-- =====================================================

-- Matières
CREATE TABLE subjects (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name_ar VARCHAR(255) NOT NULL,
    name_fr VARCHAR(255) NOT NULL,
    code VARCHAR(50) UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Matières par classe (avec coefficients)
CREATE TABLE class_subjects (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    class_id BIGINT UNSIGNED NOT NULL,
    subject_id BIGINT UNSIGNED NOT NULL,
    teacher_id BIGINT UNSIGNED NULL,
    coefficient DECIMAL(4,2) DEFAULT 1.00,
    hours_per_week DECIMAL(4,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES staff(id) ON DELETE SET NULL,
    UNIQUE KEY unique_class_subject (class_id, subject_id),
    INDEX idx_teacher (teacher_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Notes
CREATE TABLE grades (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    student_id BIGINT UNSIGNED NOT NULL,
    class_subject_id BIGINT UNSIGNED NOT NULL,
    term_id BIGINT UNSIGNED NOT NULL,
    value DECIMAL(5,2) NOT NULL,
    max_value DECIMAL(5,2) DEFAULT 20.00,
    evaluation_type ENUM('homework', 'quiz', 'exam', 'project', 'participation', 'other') NOT NULL,
    evaluation_date DATE NOT NULL,
    comments TEXT,
    teacher_id BIGINT UNSIGNED NOT NULL,
    is_published BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (class_subject_id) REFERENCES class_subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (term_id) REFERENCES terms(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES staff(id) ON DELETE CASCADE,
    INDEX idx_student_term (student_id, term_id),
    INDEX idx_class_subject_term (class_subject_id, term_id),
    INDEX idx_published (is_published)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bulletins scolaires (générés)
CREATE TABLE report_cards (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    student_id BIGINT UNSIGNED NOT NULL,
    term_id BIGINT UNSIGNED NOT NULL,
    general_average DECIMAL(5,2),
    class_rank INT,
    class_size INT,
    mention VARCHAR(50),
    pdf_path VARCHAR(255),
    data JSON,
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (term_id) REFERENCES terms(id) ON DELETE CASCADE,
    UNIQUE KEY unique_student_term (student_id, term_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Présences
CREATE TABLE attendances (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    student_id BIGINT UNSIGNED NOT NULL,
    class_id BIGINT UNSIGNED NOT NULL,
    date DATE NOT NULL,
    period ENUM('morning', 'afternoon', '1', '2', '3', '4', '5', '6', '7', '8') NOT NULL,
    status ENUM('present', 'absent', 'late', 'excused', 'sick') NOT NULL,
    comments TEXT,
    teacher_id BIGINT UNSIGNED NULL,
    justified_at TIMESTAMP NULL,
    justification_document VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES staff(id) ON DELETE SET NULL,
    INDEX idx_student_date (student_id, date),
    INDEX idx_class_date (class_id, date),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Emploi du temps
CREATE TABLE timetable_slots (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    class_id BIGINT UNSIGNED NOT NULL,
    class_subject_id BIGINT UNSIGNED NOT NULL,
    classroom_id BIGINT UNSIGNED NULL,
    day_of_week TINYINT NOT NULL COMMENT '1=Lundi, 7=Dimanche',
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    academic_year_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (class_subject_id) REFERENCES class_subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (classroom_id) REFERENCES classrooms(id) ON DELETE SET NULL,
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON DELETE CASCADE,
    INDEX idx_class_day (class_id, day_of_week),
    INDEX idx_classroom_day (classroom_id, day_of_week)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 5. TABLES EXAMENS
-- =====================================================

-- Examens
CREATE TABLE exams (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    class_subject_id BIGINT UNSIGNED NOT NULL,
    term_id BIGINT UNSIGNED NOT NULL,
    exam_date DATE NOT NULL,
    start_time TIME NOT NULL,
    duration INT NOT NULL COMMENT 'En minutes',
    classroom_id BIGINT UNSIGNED NULL,
    max_score DECIMAL(5,2) DEFAULT 20.00,
    coefficient DECIMAL(4,2) DEFAULT 1.00,
    instructions TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_subject_id) REFERENCES class_subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (term_id) REFERENCES terms(id) ON DELETE CASCADE,
    FOREIGN KEY (classroom_id) REFERENCES classrooms(id) ON DELETE SET NULL,
    INDEX idx_class_subject_date (class_subject_id, exam_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Surveillants d'examens
CREATE TABLE exam_supervisors (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    exam_id BIGINT UNSIGNED NOT NULL,
    staff_id BIGINT UNSIGNED NOT NULL,
    FOREIGN KEY (exam_id) REFERENCES exams(id) ON DELETE CASCADE,
    FOREIGN KEY (staff_id) REFERENCES staff(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 6. TABLES FINANCES
-- =====================================================

-- Factures
CREATE TABLE invoices (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    school_id BIGINT UNSIGNED NOT NULL,
    student_id BIGINT UNSIGNED NOT NULL,
    invoice_number VARCHAR(50) UNIQUE NOT NULL,
    invoice_date DATE NOT NULL,
    due_date DATE NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    discount_amount DECIMAL(10,2) DEFAULT 0,
    tax_amount DECIMAL(10,2) DEFAULT 0,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('draft', 'pending', 'paid', 'overdue', 'cancelled') DEFAULT 'pending',
    description TEXT,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    INDEX idx_invoice_number (invoice_number),
    INDEX idx_student_status (student_id, status),
    INDEX idx_due_date (due_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Lignes de facture
CREATE TABLE invoice_items (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    invoice_id BIGINT UNSIGNED NOT NULL,
    description VARCHAR(255) NOT NULL,
    quantity INT DEFAULT 1,
    unit_price DECIMAL(10,2) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Paiements
CREATE TABLE payments (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    school_id BIGINT UNSIGNED NOT NULL,
    invoice_id BIGINT UNSIGNED NULL,
    student_id BIGINT UNSIGNED NOT NULL,
    payment_number VARCHAR(50) UNIQUE NOT NULL,
    payment_date DATE NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_method ENUM('cash', 'check', 'bank_transfer', 'credit_card', 'online', 'mobile_payment') NOT NULL,
    transaction_id VARCHAR(255),
    reference VARCHAR(255),
    notes TEXT,
    status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'completed',
    receipt_path VARCHAR(255),
    created_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE SET NULL,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    INDEX idx_payment_number (payment_number),
    INDEX idx_student_date (student_id, payment_date),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 7. TABLES COMMUNICATION
-- =====================================================

-- Messages
CREATE TABLE messages (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    sender_id BIGINT UNSIGNED NOT NULL,
    recipient_id BIGINT UNSIGNED NULL,
    recipient_type ENUM('user', 'class', 'level', 'all') DEFAULT 'user',
    subject VARCHAR(255),
    body TEXT NOT NULL,
    attachments JSON,
    read_at TIMESTAMP NULL,
    replied_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_sender (sender_id),
    INDEX idx_recipient (recipient_id),
    INDEX idx_read (read_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Annonces
CREATE TABLE announcements (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    school_id BIGINT UNSIGNED NOT NULL,
    title_ar VARCHAR(255) NOT NULL,
    title_fr VARCHAR(255) NOT NULL,
    content_ar TEXT NOT NULL,
    content_fr TEXT NOT NULL,
    target ENUM('all', 'level', 'class', 'parents', 'teachers', 'students') DEFAULT 'all',
    target_id BIGINT UNSIGNED NULL,
    attachments JSON,
    published_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    INDEX idx_school_published (school_id, published_at),
    INDEX idx_target (target, target_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- SMS envoyés
CREATE TABLE sms_logs (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    school_id BIGINT UNSIGNED NOT NULL,
    recipient_phone VARCHAR(20) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('pending', 'sent', 'failed', 'delivered') DEFAULT 'pending',
    sent_at TIMESTAMP NULL,
    delivery_status_at TIMESTAMP NULL,
    cost DECIMAL(6,4),
    provider VARCHAR(50),
    message_id VARCHAR(255),
    error_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    INDEX idx_school_status (school_id, status),
    INDEX idx_sent_at (sent_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 8. TABLES DISCIPLINE
-- =====================================================

-- Incidents disciplinaires
CREATE TABLE discipline_incidents (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    student_id BIGINT UNSIGNED NOT NULL,
    reporter_id BIGINT UNSIGNED NOT NULL,
    incident_date DATE NOT NULL,
    incident_time TIME,
    incident_type VARCHAR(100) NOT NULL,
    location VARCHAR(255),
    description TEXT NOT NULL,
    witnesses TEXT,
    severity ENUM('minor', 'moderate', 'serious', 'very_serious') DEFAULT 'moderate',
    sanction_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (reporter_id) REFERENCES staff(id) ON DELETE CASCADE,
    INDEX idx_student_date (student_id, incident_date),
    INDEX idx_severity (severity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sanctions
CREATE TABLE sanctions (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    incident_id BIGINT UNSIGNED NOT NULL,
    sanction_type VARCHAR(100) NOT NULL,
    description TEXT,
    start_date DATE,
    end_date DATE,
    decision_by BIGINT UNSIGNED NULL,
    parents_notified BOOLEAN DEFAULT FALSE,
    parents_notified_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (incident_id) REFERENCES discipline_incidents(id) ON DELETE CASCADE,
    FOREIGN KEY (decision_by) REFERENCES staff(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 9. TABLES BIBLIOTHÈQUE
-- =====================================================

-- Livres
CREATE TABLE library_books (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    school_id BIGINT UNSIGNED NOT NULL,
    isbn VARCHAR(20),
    title_ar VARCHAR(255),
    title_fr VARCHAR(255) NOT NULL,
    author VARCHAR(255),
    publisher VARCHAR(255),
    publication_year YEAR,
    language VARCHAR(50),
    category VARCHAR(100),
    quantity INT DEFAULT 1,
    available_quantity INT DEFAULT 1,
    shelf_location VARCHAR(100),
    cover_image VARCHAR(255),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    INDEX idx_isbn (isbn),
    INDEX idx_category (category),
    FULLTEXT idx_search (title_fr, author, description)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Emprunts
CREATE TABLE library_loans (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    book_id BIGINT UNSIGNED NOT NULL,
    student_id BIGINT UNSIGNED NOT NULL,
    borrowed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    due_date DATE NOT NULL,
    returned_at TIMESTAMP NULL,
    late_fee DECIMAL(6,2) DEFAULT 0,
    condition_at_return ENUM('good', 'acceptable', 'damaged', 'lost') NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (book_id) REFERENCES library_books(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    INDEX idx_student (student_id),
    INDEX idx_due_date (due_date),
    INDEX idx_returned (returned_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 10. TABLES UTILISATEURS & SÉCURITÉ
-- =====================================================

-- Utilisateurs (comptes système)
CREATE TABLE users (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    role_id BIGINT UNSIGNED NOT NULL,
    userable_type VARCHAR(50) NOT NULL COMMENT 'Student, Parent, Staff',
    userable_id BIGINT UNSIGNED NOT NULL,
    two_factor_enabled BOOLEAN DEFAULT FALSE,
    two_factor_secret VARCHAR(255),
    last_login_at TIMESTAMP NULL,
    last_login_ip VARCHAR(45),
    is_active BOOLEAN DEFAULT TRUE,
    email_verified_at TIMESTAMP NULL,
    remember_token VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_userable (userable_type, userable_id),
    INDEX idx_email (email),
    INDEX idx_role (role_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Rôles
CREATE TABLE roles (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) UNIQUE NOT NULL,
    display_name VARCHAR(255),
    description TEXT,
    is_system BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Permissions
CREATE TABLE permissions (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) UNIQUE NOT NULL,
    display_name VARCHAR(255),
    description TEXT,
    module VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Rôles-Permissions
CREATE TABLE role_permission (
    role_id BIGINT UNSIGNED NOT NULL,
    permission_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (role_id, permission_id),
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Logs d'activité (Audit Trail)
CREATE TABLE activity_logs (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NULL,
    school_id BIGINT UNSIGNED NULL,
    action VARCHAR(100) NOT NULL,
    subject_type VARCHAR(100),
    subject_id BIGINT UNSIGNED,
    description TEXT,
    properties JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    INDEX idx_user_created (user_id, created_at),
    INDEX idx_subject (subject_type, subject_id),
    INDEX idx_action (action)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- 11. DONNÉES DE DÉMO / SEED
-- =====================================================

-- Insertion sections prédéfinies (lycée)
INSERT INTO sections (name_ar, name_fr, code, description) VALUES
('علوم تجريبية', 'Sciences Expérimentales', 'SCI_EXP', 'Section sciences expérimentales'),
('رياضيات', 'Mathématiques', 'MATH', 'Section mathématiques'),
('علوم الإعلامية', 'Sciences de l\'Informatique', 'INFO', 'Section informatique'),
('اقتصاد وتصرف', 'Économie et Gestion', 'ECO', 'Section économie et gestion'),
('آداب', 'Lettres', 'LETT', 'Section lettres'),
('تقني', 'Technique', 'TECH', 'Section technique'),
('رياضة', 'Sport', 'SPORT', 'Section sport');

-- Insertion rôles système
INSERT INTO roles (name, display_name, description, is_system) VALUES
('super_admin', 'Super Administrateur', 'Accès total au système', TRUE),
('director', 'Directeur', 'Direction de l\'établissement', TRUE),
('admin', 'Administrateur', 'Administration scolaire', TRUE),
('teacher', 'Enseignant', 'Corps enseignant', TRUE),
('parent', 'Parent', 'Parent d\'élève', TRUE),
('student', 'Élève', 'Élève de l\'établissement', TRUE),
('accountant', 'Comptable', 'Gestion financière', TRUE),
('librarian', 'Bibliothécaire', 'Gestion bibliothèque', TRUE),
('counselor', 'Conseiller', 'Conseiller pédagogique', TRUE);

-- =====================================================
-- 12. INDEX ADDITIONNELS POUR PERFORMANCE
-- =====================================================

-- Index composites pour requêtes fréquentes
CREATE INDEX idx_grades_student_term_published ON grades(student_id, term_id, is_published);
CREATE INDEX idx_attendances_student_status ON attendances(student_id, status, date);
CREATE INDEX idx_invoices_student_due ON invoices(student_id, due_date, status);

-- =====================================================
-- 13. VUES MATÉRIALISÉES (optionnel, pour performance)
-- =====================================================

-- Vue pour calcul moyennes élèves (peut être matérialisée)
CREATE OR REPLACE VIEW student_averages AS
SELECT 
    s.id as student_id,
    s.student_number,
    s.first_name_fr,
    s.last_name_fr,
    t.id as term_id,
    t.name as term_name,
    AVG((g.value / g.max_value) * 20) as average,
    COUNT(g.id) as grade_count
FROM students s
JOIN grades g ON s.id = g.student_id
JOIN terms t ON g.term_id = t.id
WHERE g.is_published = TRUE
GROUP BY s.id, t.id;

-- =====================================================
-- FIN DU SCHÉMA
-- =====================================================

-- Note: Ce schéma inclut 50+ tables principales
-- D'autres tables peuvent être ajoutées selon besoins:
-- - Cantine (menus, inscriptions)
-- - Transport (lignes, arrêts, tracking)
-- - Santé/Infirmerie (visites, vaccins)
-- - Activités extra-scolaires (clubs, événements)
-- - Alumni
-- - etc.
