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
                Schema::create('library_books', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->string('isbn', 20)->nullable();
            $table->string('title');
            $table->string('title_ar')->nullable();
            $table->string('author');
            $table->string('author_ar')->nullable();
            $table->string('publisher')->nullable();
            $table->year('publication_year')->nullable();
            $table->string('category');
            $table->string('language', 10)->default('fr');
            $table->integer('total_copies')->default(1);
            $table->integer('available_copies')->default(1);
            $table->string('qr_code')->unique()->nullable();
            $table->string('cover_image')->nullable();
            $table->text('description')->nullable();
            $table->enum('status', ['available', 'borrowed', 'reserved', 'lost', 'damaged'])->default('available');
            $table->timestamps();

            $table->index(['school_id', 'category']);
            $table->index('isbn');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('library_books');
    }
};
