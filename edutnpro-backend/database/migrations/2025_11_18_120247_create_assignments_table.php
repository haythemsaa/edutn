<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->string('title');
            $table->string('title_ar');
            $table->text('description')->nullable();
            $table->text('description_ar')->nullable();
            $table->string('subject');
            $table->string('class_level');
            $table->enum('type', ['homework', 'project', 'quiz', 'exam', 'reading', 'research'])->default('homework');
            $table->dateTime('due_date');
            $table->integer('total_points')->default(100);
            $table->json('attachments')->nullable();
            $table->enum('status', ['draft', 'published', 'closed'])->default('published');
            $table->boolean('allow_late_submission')->default(false);
            $table->timestamps();

            $table->index(['teacher_id', 'due_date']);
            $table->index(['school_id', 'status']);
            $table->index('class_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
