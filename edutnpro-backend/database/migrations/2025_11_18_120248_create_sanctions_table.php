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
        Schema::create('sanctions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('discipline_incident_id')->constrained('discipline_incidents')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->foreignId('assigned_by')->constrained('teachers')->onDelete('cascade');
            $table->enum('sanction_type', ['verbal_warning', 'written_warning', 'detention', 'suspension', 'expulsion', 'community_service', 'parent_meeting', 'other'])->default('verbal_warning');
            $table->string('title');
            $table->string('title_ar');
            $table->text('description')->nullable();
            $table->text('description_ar')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->integer('duration_days')->nullable();
            $table->text('conditions')->nullable();
            $table->text('conditions_ar')->nullable();
            $table->enum('status', ['active', 'completed', 'cancelled', 'appealed'])->default('active');
            $table->text('notes')->nullable();
            $table->boolean('parent_acknowledged')->default(false);
            $table->timestamp('parent_acknowledged_at')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'status']);
            $table->index(['school_id', 'sanction_type']);
            $table->index('start_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sanctions');
    }
};
