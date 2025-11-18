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
        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_ar');
            $table->text('description');
            $table->text('description_ar');
            $table->string('icon')->nullable();
            $table->string('color', 20)->default('primary');
            $table->enum('category', ['academic', 'attendance', 'behavior', 'participation', 'achievement'])->default('achievement');
            $table->json('criteria');
            $table->integer('points')->default(10);
            $table->enum('rarity', ['common', 'rare', 'epic', 'legendary'])->default('common');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('badges');
    }
};
