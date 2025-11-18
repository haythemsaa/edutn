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
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('class_subject_id')->constrained('class_subjects')->onDelete('cascade');
            $table->foreignId('term_id')->constrained('terms')->onDelete('cascade');
            $table->date('exam_date');
            $table->time('start_time');
            $table->integer('duration')->comment('En minutes');
            $table->foreignId('classroom_id')->nullable()->constrained('classrooms')->onDelete('set null');
            $table->decimal('max_score', 5, 2)->default(20.00);
            $table->decimal('coefficient', 4, 2)->default(1.00);
            $table->text('instructions')->nullable();
            $table->timestamps();

            $table->index(['class_subject_id', 'exam_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};
