<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timetable_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('timetable_id')->constrained('timetables')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
            $table->foreignId('classroom_id')->nullable()->constrained('classrooms')->onDelete('set null');
            $table->enum('day_of_week', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']);
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('period_number')->default(1);
            $table->string('color', 7)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['timetable_id', 'day_of_week']);
            $table->index(['teacher_id', 'day_of_week']);
            $table->index('subject_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timetable_entries');
    }
};