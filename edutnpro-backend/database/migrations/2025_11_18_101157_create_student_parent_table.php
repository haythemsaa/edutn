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
        Schema::create('student_parent', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('parent_id')->constrained('parents')->onDelete('cascade');
            $table->enum('relationship', ['father', 'mother', 'legal_guardian', 'other']);
            $table->boolean('is_primary_contact')->default(false);
            $table->boolean('can_pick_up')->default(true);
            $table->boolean('can_authorize_medical')->default(true);
            $table->timestamps();

            $table->unique(['student_id', 'parent_id'], 'unique_student_parent');
            $table->index('student_id');
            $table->index('parent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_parent');
    }
};
