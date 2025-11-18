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
                Schema::create('bus_routes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bus_id')->constrained('buses')->onDelete('cascade');
            $table->string('route_name');
            $table->enum('shift', ['morning', 'afternoon', 'both'])->default('both');
            $table->time('departure_time');
            $table->time('arrival_time')->nullable();
            $table->decimal('distance_km', 8, 2)->nullable();
            $table->integer('estimated_duration')->nullable()->comment('minutes');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bus_routes');
    }
};
