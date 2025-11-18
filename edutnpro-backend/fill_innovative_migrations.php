<?php

$migrations = [
    // GAMIFICATION
    '2025_11_18_114351_create_badges_table.php' => <<<'PHP'
        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_ar');
            $table->text('description');
            $table->text('description_ar');
            $table->string('icon')->nullable();
            $table->string('color', 20)->default('primary');
            $table->enum('category', ['academic', 'attendance', 'behavior', 'participation', 'achievement'])->default('achievement');
            $table->json('criteria');
            $table->integer('points')->default(10);
            $table->enum('rarity', ['common', 'rare', 'epic', 'legendary'])->default('common');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
PHP,

    '2025_11_18_114351_create_student_badges_table.php' => <<<'PHP'
        Schema::create('student_badges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('badge_id')->constrained('badges')->onDelete('cascade');
            $table->timestamp('earned_at')->useCurrent();
            $table->integer('progress')->default(100);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'badge_id']);
            $table->index('earned_at');
        });
PHP,

    '2025_11_18_114352_create_student_points_table.php' => <<<'PHP'
        Schema::create('student_points', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->unique()->constrained('students')->onDelete('cascade');
            $table->integer('total_points')->default(0);
            $table->integer('current_level')->default(1);
            $table->integer('current_streak')->default(0);
            $table->integer('longest_streak')->default(0);
            $table->integer('class_rank')->nullable();
            $table->integer('school_rank')->nullable();
            $table->date('last_activity_date')->nullable();
            $table->timestamps();

            $table->index(['school_rank', 'total_points']);
        });
PHP,

    '2025_11_18_114353_create_achievements_table.php' => <<<'PHP'
        Schema::create('achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->string('type');
            $table->string('title');
            $table->string('title_ar');
            $table->text('description')->nullable();
            $table->integer('points_earned')->default(0);
            $table->json('data')->nullable();
            $table->timestamp('achieved_at')->useCurrent();
            $table->timestamps();

            $table->index(['student_id', 'achieved_at']);
            $table->index('type');
        });
PHP,

    '2025_11_18_114353_create_leaderboards_table.php' => <<<'PHP'
        Schema::create('leaderboards', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['daily', 'weekly', 'monthly', 'term', 'yearly'])->default('weekly');
            $table->foreignId('school_id')->nullable()->constrained('schools')->onDelete('cascade');
            $table->foreignId('class_id')->nullable()->constrained('classes')->onDelete('cascade');
            $table->date('period_start');
            $table->date('period_end');
            $table->json('rankings');
            $table->timestamps();

            $table->index(['type', 'period_start', 'period_end']);
        });
PHP,

    // LIBRARY
    '2025_11_18_114402_create_library_books_table.php' => <<<'PHP'
        Schema::create('library_books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->string('isbn', 20)->nullable();
            $table->string('title');
            $table->string('title_ar')->nullable();
            $table->string('author');
            $table->string('author_ar')->nullable();
            $table->string('publisher')->nullable();
            $table->year('publication_year')->nullable();
            $table->string('category');
            $table->string('language', 10)->default('fr');
            $table->integer('total_copies')->default(1);
            $table->integer('available_copies')->default(1);
            $table->string('qr_code')->unique()->nullable();
            $table->string('cover_image')->nullable();
            $table->text('description')->nullable();
            $table->enum('status', ['available', 'borrowed', 'reserved', 'lost', 'damaged'])->default('available');
            $table->timestamps();

            $table->index(['school_id', 'category']);
            $table->index('isbn');
        });
PHP,

    '2025_11_18_114403_create_library_loans_table.php' => <<<'PHP'
        Schema::create('library_loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained('library_books')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->date('loan_date');
            $table->date('due_date');
            $table->date('return_date')->nullable();
            $table->enum('status', ['active', 'returned', 'overdue', 'lost'])->default('active');
            $table->decimal('fine_amount', 8, 2)->default(0);
            $table->boolean('fine_paid')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'status']);
            $table->index(['book_id', 'status']);
        });
PHP,

    '2025_11_18_114404_create_reading_stats_table.php' => <<<'PHP'
        Schema::create('reading_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->integer('books_read')->default(0);
            $table->integer('books_borrowed')->default(0);
            $table->integer('total_pages')->default(0);
            $table->string('favorite_category')->nullable();
            $table->integer('reading_streak')->default(0);
            $table->integer('month')->unsigned();
            $table->integer('year')->unsigned();
            $table->timestamps();

            $table->unique(['student_id', 'month', 'year']);
        });
PHP,

    // TRANSPORT
    '2025_11_18_114404_create_buses_table.php' => <<<'PHP'
        Schema::create('buses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->string('bus_number', 50)->unique();
            $table->string('license_plate', 20)->unique();
            $table->integer('capacity');
            $table->string('driver_name');
            $table->string('driver_phone', 20);
            $table->string('driver_license', 50);
            $table->string('supervisor_name')->nullable();
            $table->string('supervisor_phone', 20)->nullable();
            $table->string('gps_device_id')->nullable();
            $table->enum('status', ['active', 'maintenance', 'inactive'])->default('active');
            $table->date('insurance_expiry')->nullable();
            $table->date('inspection_date')->nullable();
            $table->timestamps();
        });
PHP,

    '2025_11_18_114405_create_bus_routes_table.php' => <<<'PHP'
        Schema::create('bus_routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bus_id')->constrained('buses')->onDelete('cascade');
            $table->string('route_name');
            $table->enum('shift', ['morning', 'afternoon', 'both'])->default('both');
            $table->time('departure_time');
            $table->time('arrival_time')->nullable();
            $table->decimal('distance_km', 8, 2)->nullable();
            $table->integer('estimated_duration')->nullable()->comment('minutes');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
PHP,

    '2025_11_18_114406_create_bus_stops_table.php' => <<<'PHP'
        Schema::create('bus_stops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('route_id')->constrained('bus_routes')->onDelete('cascade');
            $table->string('stop_name');
            $table->text('address');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->integer('stop_order')->unsigned();
            $table->time('arrival_time')->nullable();
            $table->timestamps();

            $table->index(['route_id', 'stop_order']);
        });
PHP,

    '2025_11_18_114407_create_student_transportation_table.php' => <<<'PHP'
        Schema::create('student_transportation', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('route_id')->constrained('bus_routes')->onDelete('cascade');
            $table->foreignId('pickup_stop_id')->constrained('bus_stops')->onDelete('cascade');
            $table->foreignId('dropoff_stop_id')->nullable()->constrained('bus_stops')->onDelete('cascade');
            $table->enum('shift', ['morning', 'afternoon', 'both'])->default('both');
            $table->boolean('is_active')->default(true);
            $table->decimal('monthly_fee', 8, 2)->default(0);
            $table->timestamps();

            $table->index(['student_id', 'is_active']);
        });
PHP,

    // CANTEEN
    '2025_11_18_114416_create_canteen_menus_table.php' => <<<'PHP'
        Schema::create('canteen_menus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->date('menu_date');
            $table->enum('meal_type', ['breakfast', 'lunch', 'snack', 'dinner'])->default('lunch');
            $table->string('main_dish');
            $table->string('main_dish_ar')->nullable();
            $table->string('side_dish')->nullable();
            $table->string('dessert')->nullable();
            $table->string('beverage')->nullable();
            $table->json('allergens')->nullable();
            $table->integer('calories')->nullable();
            $table->string('photo')->nullable();
            $table->decimal('price', 8, 2)->default(0);
            $table->boolean('is_available')->default(true);
            $table->timestamps();

            $table->unique(['school_id', 'menu_date', 'meal_type']);
        });
PHP,

    '2025_11_18_114416_create_meal_reservations_table.php' => <<<'PHP'
        Schema::create('meal_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('menu_id')->constrained('canteen_menus')->onDelete('cascade');
            $table->date('reservation_date');
            $table->enum('status', ['reserved', 'consumed', 'cancelled', 'no_show'])->default('reserved');
            $table->decimal('amount_paid', 8, 2)->default(0);
            $table->boolean('paid')->default(false);
            $table->timestamp('consumed_at')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'menu_id', 'reservation_date']);
            $table->index(['reservation_date', 'status']);
        });
PHP,

    '2025_11_18_114417_create_dietary_restrictions_table.php' => <<<'PHP'
        Schema::create('dietary_restrictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->enum('type', ['allergy', 'intolerance', 'religious', 'vegetarian', 'vegan', 'other'])->default('allergy');
            $table->string('restriction');
            $table->text('description')->nullable();
            $table->enum('severity', ['mild', 'moderate', 'severe'])->nullable();
            $table->text('special_instructions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['student_id', 'is_active']);
        });
PHP,

    // NOTIFICATIONS
    '2025_11_18_114418_create_notifications_table.php' => <<<'PHP'
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('type');
            $table->string('title');
            $table->string('title_ar')->nullable();
            $table->text('message');
            $table->text('message_ar')->nullable();
            $table->json('data')->nullable();
            $table->enum('channel', ['app', 'email', 'sms', 'push'])->default('app');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'is_read']);
            $table->index(['type', 'created_at']);
        });
PHP,

    '2025_11_18_114418_create_notification_preferences_table.php' => <<<'PHP'
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->boolean('email_enabled')->default(true);
            $table->boolean('sms_enabled')->default(false);
            $table->boolean('push_enabled')->default(true);
            $table->json('notification_types')->nullable();
            $table->time('quiet_hours_start')->nullable();
            $table->time('quiet_hours_end')->nullable();
            $table->string('preferred_language', 10)->default('fr');
            $table->timestamps();
        });
PHP,

    // ANALYTICS
    '2025_11_18_114419_create_analytics_snapshots_table.php' => <<<'PHP'
        Schema::create('analytics_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->date('snapshot_date');
            $table->integer('total_students')->default(0);
            $table->integer('present_students')->default(0);
            $table->integer('absent_students')->default(0);
            $table->decimal('attendance_rate', 5, 2)->default(0);
            $table->decimal('average_grade', 5, 2)->nullable();
            $table->integer('new_enrollments')->default(0);
            $table->integer('withdrawals')->default(0);
            $table->decimal('revenue', 10, 2)->default(0);
            $table->decimal('expenses', 10, 2)->default(0);
            $table->json('additional_metrics')->nullable();
            $table->timestamps();

            $table->unique(['school_id', 'snapshot_date']);
        });
PHP,

    '2025_11_18_114420_create_predictions_table.php' => <<<'PHP'
        Schema::create('predictions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->enum('prediction_type', ['performance', 'attendance', 'dropout_risk', 'achievement'])->default('performance');
            $table->decimal('probability', 5, 2)->comment('0-100%');
            $table->decimal('confidence_score', 5, 2)->comment('0-100%');
            $table->text('factors')->nullable();
            $table->json('recommendations')->nullable();
            $table->enum('risk_level', ['low', 'medium', 'high', 'critical'])->nullable();
            $table->date('prediction_date');
            $table->date('target_date')->nullable();
            $table->boolean('was_accurate')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'prediction_type']);
            $table->index(['risk_level', 'prediction_date']);
        });
PHP,
];

foreach ($migrations as $filename => $schema) {
    $filepath = __DIR__ . "/database/migrations/{$filename}";

    if (!file_exists($filepath)) {
        echo "❌ File not found: {$filename}\n";
        continue;
    }

    $content = file_get_contents($filepath);

    // Replace the empty up() method with the schema
    $pattern = '/public function up\(\): void\s*\{[^}]*\}/s';
    $replacement = "public function up(): void\n    {\n        {$schema}\n    }";

    $content = preg_replace($pattern, $replacement, $content);

    file_put_contents($filepath, $content);
    echo "✅ Updated: {$filename}\n";
}

echo "\n🎉 All innovative migrations have been updated!\n";
