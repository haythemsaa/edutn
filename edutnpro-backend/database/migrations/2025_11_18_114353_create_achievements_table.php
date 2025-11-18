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
                Schema::create('achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->string('type');
            $table->string('title');
            $table->string('title_ar');
            $table->text('description')->nullable();
            $table->integer('points_earned')->default(0);
            $table->json('data')->nullable();
            $table->timestamp('achieved_at')->useCurrent();
            $table->timestamps();

            $table->index(['student_id', 'achieved_at']);
            $table->index('type');
        });
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('achievements');
    }
};
