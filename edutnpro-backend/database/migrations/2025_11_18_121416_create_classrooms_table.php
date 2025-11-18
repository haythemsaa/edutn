<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classrooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->string('name');
            $table->string('name_ar');
            $table->string('building')->nullable();
            $table->string('floor')->nullable();
            $table->integer('capacity')->default(30);
            $table->enum('type', ['standard', 'lab', 'computer', 'art', 'music', 'sports', 'library', 'auditorium'])->default('standard');
            $table->json('equipment')->nullable();
            $table->boolean('has_projector')->default(false);
            $table->boolean('has_computer')->default(false);
            $table->boolean('has_ac')->default(false);
            $table->boolean('is_accessible')->default(true);
            $table->boolean('is_available')->default(true);
            $table->timestamps();

            $table->index(['school_id', 'type']);
            $table->index('is_available');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classrooms');
    }
};