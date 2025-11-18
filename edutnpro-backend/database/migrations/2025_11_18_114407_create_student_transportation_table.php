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
                Schema::create('student_transportation', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('route_id')->constrained('bus_routes')->onDelete('cascade');
            $table->foreignId('pickup_stop_id')->constrained('bus_stops')->onDelete('cascade');
            $table->foreignId('dropoff_stop_id')->nullable()->constrained('bus_stops')->onDelete('cascade');
            $table->enum('shift', ['morning', 'afternoon', 'both'])->default('both');
            $table->boolean('is_active')->default(true);
            $table->decimal('monthly_fee', 8, 2)->default(0);
            $table->timestamps();

            $table->index(['student_id', 'is_active']);
        });
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_transportation');
    }
};
