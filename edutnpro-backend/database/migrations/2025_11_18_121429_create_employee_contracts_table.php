<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->string('contract_number')->unique();
            $table->enum('contract_type', ['permanent', 'fixed_term', 'temporary', 'part_time', 'consultant', 'intern'])->default('fixed_term');
            $table->string('position');
            $table->string('position_ar');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->decimal('salary', 10, 2);
            $table->string('currency', 3)->default('TND');
            $table->enum('payment_frequency', ['monthly', 'bi_weekly', 'weekly', 'hourly'])->default('monthly');
            $table->integer('weekly_hours')->default(40);
            $table->text('responsibilities')->nullable();
            $table->text('responsibilities_ar')->nullable();
            $table->text('benefits')->nullable();
            $table->json('allowances')->nullable();
            $table->enum('status', ['draft', 'active', 'expired', 'terminated', 'renewed'])->default('draft');
            $table->date('signed_date')->nullable();
            $table->string('file_path')->nullable();
            $table->text('termination_reason')->nullable();
            $table->date('termination_date')->nullable();
            $table->timestamps();

            $table->index(['teacher_id', 'status']);
            $table->index(['school_id', 'contract_type']);
            $table->index(['start_date', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_contracts');
    }
};