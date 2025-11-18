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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('predictions');
    }
};
