<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->string('name');
            $table->string('name_ar');
            $table->string('code', 20)->unique();
            $table->text('description')->nullable();
            $table->text('description_ar')->nullable();
            $table->enum('category', ['core', 'optional', 'language', 'science', 'arts', 'sports', 'technical'])->default('core');
            $table->integer('coefficient')->default(1);
            $table->string('color', 7)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['school_id', 'is_active']);
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};