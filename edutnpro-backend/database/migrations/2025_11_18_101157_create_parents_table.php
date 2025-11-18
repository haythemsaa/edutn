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
        Schema::create('parents', function (Blueprint $table) {
            $table->id();
            $table->string('cin', 20)->unique();
            $table->string('first_name_ar', 100);
            $table->string('last_name_ar', 100);
            $table->string('first_name_fr', 100);
            $table->string('last_name_fr', 100);
            $table->date('date_of_birth')->nullable();
            $table->string('phone_mobile', 20);
            $table->string('phone_work', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('profession')->nullable();
            $table->string('employer')->nullable();
            $table->text('work_address')->nullable();
            $table->decimal('monthly_income', 10, 2)->nullable();
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->timestamps();

            $table->index('cin');
            $table->index('phone_mobile');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parents');
    }
};
