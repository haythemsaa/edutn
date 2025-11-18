<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_card_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_card_id')->constrained('report_cards')->onDelete('cascade');
            $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
            $table->decimal('grade', 5, 2);
            $table->decimal('class_average', 5, 2)->nullable();
            $table->decimal('highest_grade', 5, 2)->nullable();
            $table->decimal('lowest_grade', 5, 2)->nullable();
            $table->integer('coefficient')->default(1);
            $table->decimal('weighted_grade', 5, 2)->nullable();
            $table->text('teacher_comment')->nullable();
            $table->text('teacher_comment_ar')->nullable();
            $table->enum('appreciation', ['excellent', 'very_good', 'good', 'satisfactory', 'needs_improvement', 'insufficient'])->nullable();
            $table->timestamps();

            $table->index(['report_card_id', 'subject_id']);
            $table->index('appreciation');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_card_items');
    }
};