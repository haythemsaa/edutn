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
                Schema::create('meal_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('menu_id')->constrained('canteen_menus')->onDelete('cascade');
            $table->date('reservation_date');
            $table->enum('status', ['reserved', 'consumed', 'cancelled', 'no_show'])->default('reserved');
            $table->decimal('amount_paid', 8, 2)->default(0);
            $table->boolean('paid')->default(false);
            $table->timestamp('consumed_at')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'menu_id', 'reservation_date']);
            $table->index(['reservation_date', 'status']);
        });
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meal_reservations');
    }
};
