<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_diaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_section_id')->constrained('class_sections')->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->date('lesson_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('lesson_title');
            $table->string('lesson_title_ar');
            $table->text('lesson_content');
            $table->text('lesson_content_ar');
            $table->text('objectives')->nullable();
            $table->text('objectives_ar')->nullable();
            $table->text('homework')->nullable();
            $table->text('homework_ar')->nullable();
            $table->json('attachments')->nullable();
            $table->integer('students_present')->default(0);
            $table->integer('students_absent')->default(0);
            $table->text('notes')->nullable();
            $table->enum('status', ['draft', 'published'])->default('published');
            $table->timestamps();

            $table->index(['class_section_id', 'lesson_date']);
            $table->index(['teacher_id', 'subject_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('class_diaries');
    }
};