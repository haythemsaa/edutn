<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('class_section_id')->constrained('class_sections')->onDelete('cascade');
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->string('academic_year', 20);
            $table->enum('term', ['trimester_1', 'trimester_2', 'trimester_3', 'semester_1', 'semester_2', 'annual'])->default('trimester_1');
            $table->decimal('total_average', 5, 2)->default(0);
            $table->integer('class_rank')->nullable();
            $table->integer('total_students')->nullable();
            $table->integer('total_absences')->default(0);
            $table->text('general_appreciation')->nullable();
            $table->text('general_appreciation_ar')->nullable();
            $table->text('conduct_comment')->nullable();
            $table->text('conduct_comment_ar')->nullable();
            $table->enum('status', ['draft', 'finalized', 'published', 'sent'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->string('file_path')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'academic_year', 'term']);
            $table->index(['class_section_id', 'term']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_cards');
    }
};