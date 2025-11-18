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
                Schema::create('canteen_menus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->date('menu_date');
            $table->enum('meal_type', ['breakfast', 'lunch', 'snack', 'dinner'])->default('lunch');
            $table->string('main_dish');
            $table->string('main_dish_ar')->nullable();
            $table->string('side_dish')->nullable();
            $table->string('dessert')->nullable();
            $table->string('beverage')->nullable();
            $table->json('allergens')->nullable();
            $table->integer('calories')->nullable();
            $table->string('photo')->nullable();
            $table->decimal('price', 8, 2)->default(0);
            $table->boolean('is_available')->default(true);
            $table->timestamps();

            $table->unique(['school_id', 'menu_date', 'meal_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('canteen_menus');
    }
};
