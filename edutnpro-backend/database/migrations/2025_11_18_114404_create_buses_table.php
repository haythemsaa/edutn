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
                Schema::create('buses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->string('bus_number', 50)->unique();
            $table->string('license_plate', 20)->unique();
            $table->integer('capacity');
            $table->string('driver_name');
            $table->string('driver_phone', 20);
            $table->string('driver_license', 50);
            $table->string('supervisor_name')->nullable();
            $table->string('supervisor_phone', 20)->nullable();
            $table->string('gps_device_id')->nullable();
            $table->enum('status', ['active', 'maintenance', 'inactive'])->default('active');
            $table->date('insurance_expiry')->nullable();
            $table->date('inspection_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('buses');
    }
};
