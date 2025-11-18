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
        Schema::create('discipline_incidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->foreignId('reported_by')->constrained('teachers')->onDelete('cascade');
            $table->string('title');
            $table->string('title_ar');
            $table->text('description');
            $table->text('description_ar');
            $table->enum('incident_type', ['tardiness', 'absence', 'misbehavior', 'violence', 'cheating', 'disrespect', 'vandalism', 'other'])->default('misbehavior');
            $table->date('incident_date');
            $table->time('incident_time')->nullable();
            $table->string('location')->nullable();
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->text('witnesses')->nullable();
            $table->text('action_taken')->nullable();
            $table->text('action_taken_ar')->nullable();
            $table->enum('status', ['reported', 'under_review', 'resolved', 'escalated'])->default('reported');
            $table->boolean('parent_notified')->default(false);
            $table->timestamp('parent_notified_at')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'incident_date']);
            $table->index(['school_id', 'severity']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discipline_incidents');
    }
};
