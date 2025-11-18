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
                Schema::create('reading_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->integer('books_read')->default(0);
            $table->integer('books_borrowed')->default(0);
            $table->integer('total_pages')->default(0);
            $table->string('favorite_category')->nullable();
            $table->integer('reading_streak')->default(0);
            $table->integer('month')->unsigned();
            $table->integer('year')->unsigned();
            $table->timestamps();

            $table->unique(['student_id', 'month', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reading_stats');
    }
};
