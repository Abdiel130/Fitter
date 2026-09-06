<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_food_logs', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('food_item_id')->nullable()->constrained('food_items')->cascadeOnDelete();
            $table->foreignUuid('recipe_id')->nullable()->constrained('recipes')->cascadeOnDelete();
            $table->date('log_date');
            $table->string('meal_type', 20)->comment('breakfast, lunch, dinner, snack');
            $table->decimal('amount_in_grams', 6, 2);
            $table->decimal('calculated_calories', 6, 1);
            $table->decimal('calculated_protein', 5, 1);
            $table->decimal('calculated_carbs', 5, 1);
            $table->decimal('calculated_fat', 5, 1);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_food_logs');
    }
};
