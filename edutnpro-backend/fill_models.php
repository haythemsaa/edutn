<?php

$modelsConfig = [
    'ParentModel' => [
        'table' => 'parents',
        'fillable' => ['cin', 'first_name_ar', 'last_name_ar', 'first_name_fr', 'last_name_fr', 'date_of_birth', 'phone_mobile', 'phone_work', 'email', 'profession', 'employer', 'work_address', 'monthly_income', 'address', 'city', 'postal_code'],
        'casts' => ['date_of_birth' => 'date', 'monthly_income' => 'decimal:2'],
        'relationships' => [
            'students' => 'belongsToMany(Student::class, \'student_parent\')->withPivot(\'relationship\', \'is_primary_contact\', \'can_pick_up\', \'can_authorize_medical\')->withTimestamps()'
        ]
    ],
    'Classroom' => [
        'fillable' => ['school_id', 'name', 'room_number', 'capacity', 'room_type', 'equipment'],
        'casts' => ['equipment' => 'array', 'capacity' => 'integer'],
        'relationships' => [
            'school' => 'belongsTo(School::class)',
            'classes' => 'hasMany(ClassRoom::class, \'classroom_id\')'
        ]
    ],
    'Attendance' => [
        'fillable' => ['student_id', 'class_id', 'date', 'period', 'status', 'comments', 'teacher_id', 'justified_at', 'justification_document'],
        'casts' => ['date' => 'date', 'justified_at' => 'datetime'],
        'relationships' => [
            'student' => 'belongsTo(Student::class)',
            'class' => 'belongsTo(ClassRoom::class)',
            'teacher' => 'belongsTo(Teacher::class)'
        ]
    ],
    'Invoice' => [
        'fillable' => ['school_id', 'student_id', 'invoice_number', 'invoice_date', 'due_date', 'amount', 'discount_amount', 'tax_amount', 'total_amount', 'status', 'description', 'notes'],
        'casts' => ['invoice_date' => 'date', 'due_date' => 'date', 'amount' => 'decimal:2', 'discount_amount' => 'decimal:2', 'tax_amount' => 'decimal:2', 'total_amount' => 'decimal:2'],
        'relationships' => [
            'school' => 'belongsTo(School::class)',
            'student' => 'belongsTo(Student::class)',
            'items' => 'hasMany(InvoiceItem::class)',
            'payments' => 'hasMany(Payment::class)'
        ]
    ],
    'InvoiceItem' => [
        'fillable' => ['invoice_id', 'description', 'quantity', 'unit_price', 'amount'],
        'casts' => ['quantity' => 'integer', 'unit_price' => 'decimal:2', 'amount' => 'decimal:2'],
        'relationships' => [
            'invoice' => 'belongsTo(Invoice::class)'
        ]
    ],
    'Payment' => [
        'fillable' => ['school_id', 'invoice_id', 'student_id', 'payment_number', 'payment_date', 'amount', 'payment_method', 'transaction_id', 'reference', 'notes', 'status', 'receipt_path', 'created_by'],
        'casts' => ['payment_date' => 'date', 'amount' => 'decimal:2'],
        'relationships' => [
            'school' => 'belongsTo(School::class)',
            'invoice' => 'belongsTo(Invoice::class)',
            'student' => 'belongsTo(Student::class)',
            'creator' => 'belongsTo(User::class, \'created_by\')'
        ]
    ],
    'Message' => [
        'fillable' => ['sender_id', 'recipient_id', 'recipient_type', 'subject', 'body', 'attachments', 'read_at', 'replied_at'],
        'casts' => ['attachments' => 'array', 'read_at' => 'datetime', 'replied_at' => 'datetime'],
        'relationships' => [
            'sender' => 'belongsTo(User::class, \'sender_id\')',
            'recipient' => 'belongsTo(User::class, \'recipient_id\')'
        ]
    ],
    'Announcement' => [
        'fillable' => ['school_id', 'title_ar', 'title_fr', 'content_ar', 'content_fr', 'target', 'target_id', 'attachments', 'published_at', 'expires_at', 'created_by'],
        'casts' => ['attachments' => 'array', 'published_at' => 'datetime', 'expires_at' => 'datetime'],
        'relationships' => [
            'school' => 'belongsTo(School::class)',
            'creator' => 'belongsTo(User::class, \'created_by\')'
        ]
    ],
    'Exam' => [
        'fillable' => ['name', 'class_subject_id', 'term_id', 'exam_date', 'start_time', 'duration', 'classroom_id', 'max_score', 'coefficient', 'instructions'],
        'casts' => ['exam_date' => 'date', 'duration' => 'integer', 'max_score' => 'decimal:2', 'coefficient' => 'decimal:2'],
        'relationships' => [
            'classSubject' => 'belongsTo(ClassSubject::class)',
            'term' => 'belongsTo(Term::class)',
            'classroom' => 'belongsTo(Classroom::class)',
            'supervisors' => 'belongsToMany(Teacher::class, \'exam_supervisors\')->withTimestamps()'
        ]
    ],
    'ExamSupervisor' => [
        'table' => 'exam_supervisors',
        'fillable' => ['exam_id', 'teacher_id'],
        'relationships' => [
            'exam' => 'belongsTo(Exam::class)',
            'teacher' => 'belongsTo(Teacher::class)'
        ]
    ],
    'TimetableSlot' => [
        'fillable' => ['class_id', 'class_subject_id', 'classroom_id', 'day_of_week', 'start_time', 'end_time', 'academic_year_id'],
        'casts' => ['day_of_week' => 'integer'],
        'relationships' => [
            'class' => 'belongsTo(ClassRoom::class)',
            'classSubject' => 'belongsTo(ClassSubject::class)',
            'classroom' => 'belongsTo(Classroom::class)',
            'academicYear' => 'belongsTo(AcademicYear::class)'
        ]
    ],
    'ClassSubject' => [
        'fillable' => ['class_id', 'subject_id', 'teacher_id', 'coefficient', 'hours_per_week'],
        'casts' => ['coefficient' => 'decimal:2', 'hours_per_week' => 'decimal:2'],
        'relationships' => [
            'class' => 'belongsTo(ClassRoom::class)',
            'subject' => 'belongsTo(Subject::class)',
            'teacher' => 'belongsTo(Teacher::class)',
            'exams' => 'hasMany(Exam::class)',
            'timetableSlots' => 'hasMany(TimetableSlot::class)'
        ]
    ],
    'ReportCard' => [
        'fillable' => ['student_id', 'term_id', 'general_average', 'class_rank', 'class_size', 'mention', 'pdf_path', 'data', 'generated_at'],
        'casts' => ['general_average' => 'decimal:2', 'class_rank' => 'integer', 'class_size' => 'integer', 'data' => 'array', 'generated_at' => 'datetime'],
        'relationships' => [
            'student' => 'belongsTo(Student::class)',
            'term' => 'belongsTo(Term::class)'
        ]
    ],
    'EmergencyContact' => [
        'fillable' => ['student_id', 'name', 'relationship', 'phone', 'priority'],
        'casts' => ['priority' => 'integer'],
        'relationships' => [
            'student' => 'belongsTo(Student::class)'
        ]
    ]
];

foreach ($modelsConfig as $modelName => $config) {
    $modelPath = __DIR__ . "/app/Models/{$modelName}.php";

    $fillable = json_encode($config['fillable'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    $fillable = str_replace(['"', '{', '}'], ['\'', '[', ']'], $fillable);

    $casts = isset($config['casts']) ? json_encode($config['casts'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : '[]';
    $casts = str_replace(['":', '",', '{', '}', '"'], ['\' =>', '\',', '[', ']', '\''], $casts);

    $table = isset($config['table']) ? "\n    protected \$table = '{$config['table']}';\n" : '';

    $relationships = '';
    if (isset($config['relationships'])) {
        foreach ($config['relationships'] as $relName => $relDef) {
            $relationships .= "\n    public function {$relName}()\n    {\n        return \$this->{$relDef};\n    }\n";
        }
    }

    $content = "<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\\*;

class {$modelName} extends Model
{{$table}
    protected \$fillable = {$fillable};

    protected \$casts = {$casts};
{$relationships}}
";

    file_put_contents($modelPath, $content);
    echo "✓ {$modelName} model updated\n";
}

echo "\n✅ All models have been updated!\n";
