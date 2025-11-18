<?php

$modelsConfig = [
    'Badge' => [
        'fillable' => ['name', 'name_ar', 'description', 'description_ar', 'icon', 'color', 'category', 'criteria', 'points', 'rarity', 'is_active'],
        'casts' => ['criteria' => 'array', 'is_active' => 'boolean', 'points' => 'integer'],
        'relationships' => [
            'studentBadges' => 'hasMany(StudentBadge::class)',
            'students' => 'belongsToMany(Student::class, \'student_badges\')->withPivot(\'earned_at\', \'progress\')->withTimestamps()'
        ]
    ],
    'StudentBadge' => [
        'fillable' => ['student_id', 'badge_id', 'earned_at', 'progress', 'metadata'],
        'casts' => ['earned_at' => 'datetime', 'progress' => 'integer', 'metadata' => 'array'],
        'relationships' => [
            'student' => 'belongsTo(Student::class)',
            'badge' => 'belongsTo(Badge::class)'
        ]
    ],
    'StudentPoint' => [
        'fillable' => ['student_id', 'total_points', 'current_level', 'current_streak', 'longest_streak', 'class_rank', 'school_rank', 'last_activity_date'],
        'casts' => ['total_points' => 'integer', 'current_level' => 'integer', 'current_streak' => 'integer', 'longest_streak' => 'integer', 'class_rank' => 'integer', 'school_rank' => 'integer', 'last_activity_date' => 'date'],
        'relationships' => [
            'student' => 'belongsTo(Student::class)'
        ]
    ],
    'Achievement' => [
        'fillable' => ['student_id', 'type', 'title', 'title_ar', 'description', 'points_earned', 'data', 'achieved_at'],
        'casts' => ['points_earned' => 'integer', 'data' => 'array', 'achieved_at' => 'datetime'],
        'relationships' => [
            'student' => 'belongsTo(Student::class)'
        ]
    ],
    'Leaderboard' => [
        'fillable' => ['type', 'school_id', 'class_id', 'period_start', 'period_end', 'rankings'],
        'casts' => ['period_start' => 'date', 'period_end' => 'date', 'rankings' => 'array'],
        'relationships' => [
            'school' => 'belongsTo(School::class)',
            'class' => 'belongsTo(ClassRoom::class)'
        ]
    ],
    'LibraryBook' => [
        'fillable' => ['school_id', 'isbn', 'title', 'title_ar', 'author', 'author_ar', 'publisher', 'publication_year', 'category', 'language', 'total_copies', 'available_copies', 'qr_code', 'cover_image', 'description', 'status'],
        'casts' => ['publication_year' => 'integer', 'total_copies' => 'integer', 'available_copies' => 'integer'],
        'relationships' => [
            'school' => 'belongsTo(School::class)',
            'loans' => 'hasMany(LibraryLoan::class, \'book_id\')'
        ]
    ],
    'LibraryLoan' => [
        'fillable' => ['book_id', 'student_id', 'loan_date', 'due_date', 'return_date', 'status', 'fine_amount', 'fine_paid', 'notes'],
        'casts' => ['loan_date' => 'date', 'due_date' => 'date', 'return_date' => 'date', 'fine_amount' => 'decimal:2', 'fine_paid' => 'boolean'],
        'relationships' => [
            'book' => 'belongsTo(LibraryBook::class)',
            'student' => 'belongsTo(Student::class)'
        ]
    ],
    'ReadingStat' => [
        'fillable' => ['student_id', 'books_read', 'books_borrowed', 'total_pages', 'favorite_category', 'reading_streak', 'month', 'year'],
        'casts' => ['books_read' => 'integer', 'books_borrowed' => 'integer', 'total_pages' => 'integer', 'reading_streak' => 'integer', 'month' => 'integer', 'year' => 'integer'],
        'relationships' => [
            'student' => 'belongsTo(Student::class)'
        ]
    ],
    'Bus' => [
        'fillable' => ['school_id', 'bus_number', 'license_plate', 'capacity', 'driver_name', 'driver_phone', 'driver_license', 'supervisor_name', 'supervisor_phone', 'gps_device_id', 'status', 'insurance_expiry', 'inspection_date'],
        'casts' => ['capacity' => 'integer', 'insurance_expiry' => 'date', 'inspection_date' => 'date'],
        'relationships' => [
            'school' => 'belongsTo(School::class)',
            'routes' => 'hasMany(BusRoute::class)'
        ]
    ],
    'BusRoute' => [
        'fillable' => ['bus_id', 'route_name', 'shift', 'departure_time', 'arrival_time', 'distance_km', 'estimated_duration', 'is_active'],
        'casts' => ['distance_km' => 'decimal:2', 'estimated_duration' => 'integer', 'is_active' => 'boolean'],
        'relationships' => [
            'bus' => 'belongsTo(Bus::class)',
            'stops' => 'hasMany(BusStop::class, \'route_id\')',
            'studentTransportations' => 'hasMany(StudentTransportation::class, \'route_id\')'
        ]
    ],
    'BusStop' => [
        'fillable' => ['route_id', 'stop_name', 'address', 'latitude', 'longitude', 'stop_order', 'arrival_time'],
        'casts' => ['latitude' => 'decimal:8', 'longitude' => 'decimal:8', 'stop_order' => 'integer'],
        'relationships' => [
            'route' => 'belongsTo(BusRoute::class)'
        ]
    ],
    'StudentTransportation' => [
        'fillable' => ['student_id', 'route_id', 'pickup_stop_id', 'dropoff_stop_id', 'shift', 'is_active', 'monthly_fee'],
        'casts' => ['is_active' => 'boolean', 'monthly_fee' => 'decimal:2'],
        'relationships' => [
            'student' => 'belongsTo(Student::class)',
            'route' => 'belongsTo(BusRoute::class)',
            'pickupStop' => 'belongsTo(BusStop::class, \'pickup_stop_id\')',
            'dropoffStop' => 'belongsTo(BusStop::class, \'dropoff_stop_id\')'
        ]
    ],
    'CanteenMenu' => [
        'fillable' => ['school_id', 'menu_date', 'meal_type', 'main_dish', 'main_dish_ar', 'side_dish', 'dessert', 'beverage', 'allergens', 'calories', 'photo', 'price', 'is_available'],
        'casts' => ['menu_date' => 'date', 'allergens' => 'array', 'calories' => 'integer', 'price' => 'decimal:2', 'is_available' => 'boolean'],
        'relationships' => [
            'school' => 'belongsTo(School::class)',
            'reservations' => 'hasMany(MealReservation::class, \'menu_id\')'
        ]
    ],
    'MealReservation' => [
        'fillable' => ['student_id', 'menu_id', 'reservation_date', 'status', 'amount_paid', 'paid', 'consumed_at'],
        'casts' => ['reservation_date' => 'date', 'amount_paid' => 'decimal:2', 'paid' => 'boolean', 'consumed_at' => 'datetime'],
        'relationships' => [
            'student' => 'belongsTo(Student::class)',
            'menu' => 'belongsTo(CanteenMenu::class)'
        ]
    ],
    'DietaryRestriction' => [
        'fillable' => ['student_id', 'type', 'restriction', 'description', 'severity', 'special_instructions', 'is_active'],
        'casts' => ['is_active' => 'boolean'],
        'relationships' => [
            'student' => 'belongsTo(Student::class)'
        ]
    ],
    'Notification' => [
        'fillable' => ['user_id', 'type', 'title', 'title_ar', 'message', 'message_ar', 'data', 'channel', 'priority', 'is_read', 'read_at', 'sent_at'],
        'casts' => ['data' => 'array', 'is_read' => 'boolean', 'read_at' => 'datetime', 'sent_at' => 'datetime'],
        'relationships' => [
            'user' => 'belongsTo(User::class)'
        ]
    ],
    'NotificationPreference' => [
        'fillable' => ['user_id', 'email_enabled', 'sms_enabled', 'push_enabled', 'notification_types', 'quiet_hours_start', 'quiet_hours_end', 'preferred_language'],
        'casts' => ['email_enabled' => 'boolean', 'sms_enabled' => 'boolean', 'push_enabled' => 'boolean', 'notification_types' => 'array'],
        'relationships' => [
            'user' => 'belongsTo(User::class)'
        ]
    ],
    'AnalyticsSnapshot' => [
        'fillable' => ['school_id', 'snapshot_date', 'total_students', 'present_students', 'absent_students', 'attendance_rate', 'average_grade', 'new_enrollments', 'withdrawals', 'revenue', 'expenses', 'additional_metrics'],
        'casts' => ['snapshot_date' => 'date', 'total_students' => 'integer', 'present_students' => 'integer', 'absent_students' => 'integer', 'attendance_rate' => 'decimal:2', 'average_grade' => 'decimal:2', 'new_enrollments' => 'integer', 'withdrawals' => 'integer', 'revenue' => 'decimal:2', 'expenses' => 'decimal:2', 'additional_metrics' => 'array'],
        'relationships' => [
            'school' => 'belongsTo(School::class)'
        ]
    ],
    'Prediction' => [
        'fillable' => ['student_id', 'prediction_type', 'probability', 'confidence_score', 'factors', 'recommendations', 'risk_level', 'prediction_date', 'target_date', 'was_accurate'],
        'casts' => ['probability' => 'decimal:2', 'confidence_score' => 'decimal:2', 'recommendations' => 'array', 'prediction_date' => 'date', 'target_date' => 'date', 'was_accurate' => 'boolean'],
        'relationships' => [
            'student' => 'belongsTo(Student::class)'
        ]
    ],
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

echo "\n✅ All innovative models have been updated!\n";
