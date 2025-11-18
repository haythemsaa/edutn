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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('class_id')->constrained('classes')->onDelete('cascade');
            $table->date('date');
            $table->enum('period', ['morning', 'afternoon', '1', '2', '3', '4', '5', '6', '7', '8']);
            $table->enum('status', ['present', 'absent', 'late', 'excused', 'sick']);
            $table->text('comments')->nullable();
            $table->foreignId('teacher_id')->nullable()->constrained('teachers')->onDelete('set null');
            $table->timestamp('justified_at')->nullable();
            $table->string('justification_document')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'date']);
            $table->index(['class_id', 'date']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
