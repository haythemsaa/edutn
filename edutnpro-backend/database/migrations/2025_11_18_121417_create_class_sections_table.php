<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->foreignId('classroom_id')->nullable()->constrained('classrooms')->onDelete('set null');
            $table->string('name');
            $table->string('name_ar');
            $table->string('grade_level');
            $table->string('section', 10);
            $table->foreignId('class_teacher_id')->nullable()->constrained('teachers')->onDelete('set null');
            $table->string('academic_year', 20);
            $table->integer('max_students')->default(30);
            $table->integer('current_students')->default(0);
            $table->enum('shift', ['morning', 'afternoon', 'evening'])->default('morning');
            $table->enum('status', ['active', 'inactive', 'archived'])->default('active');
            $table->timestamps();

            $table->index(['school_id', 'grade_level']);
            $table->index(['academic_year', 'status']);
            $table->index('class_teacher_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_sections');
    }
};