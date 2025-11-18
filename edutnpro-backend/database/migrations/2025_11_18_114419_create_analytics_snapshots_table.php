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
                Schema::create('analytics_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->date('snapshot_date');
            $table->integer('total_students')->default(0);
            $table->integer('present_students')->default(0);
            $table->integer('absent_students')->default(0);
            $table->decimal('attendance_rate', 5, 2)->default(0);
            $table->decimal('average_grade', 5, 2)->nullable();
            $table->integer('new_enrollments')->default(0);
            $table->integer('withdrawals')->default(0);
            $table->decimal('revenue', 10, 2)->default(0);
            $table->decimal('expenses', 10, 2)->default(0);
            $table->json('additional_metrics')->nullable();
            $table->timestamps();

            $table->unique(['school_id', 'snapshot_date']);
        });
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analytics_snapshots');
    }
};
