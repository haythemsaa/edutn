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
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->enum('certificate_type', ['attendance', 'achievement', 'completion', 'conduct', 'honor_roll', 'participation', 'sports', 'academic_excellence', 'other'])->default('achievement');
            $table->string('certificate_number')->unique();
            $table->string('title');
            $table->string('title_ar');
            $table->text('description')->nullable();
            $table->text('description_ar')->nullable();
            $table->date('issued_date');
            $table->foreignId('issued_by')->constrained('users')->onDelete('cascade');
            $table->string('academic_year', 20);
            $table->string('template')->nullable();
            $table->string('file_path')->nullable();
            $table->string('digital_signature')->nullable();
            $table->json('metadata')->nullable();
            $table->enum('status', ['draft', 'issued', 'revoked', 'expired'])->default('draft');
            $table->date('valid_until')->nullable();
            $table->text('revocation_reason')->nullable();
            $table->timestamp('downloaded_at')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'certificate_type']);
            $table->index(['school_id', 'issued_date']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
