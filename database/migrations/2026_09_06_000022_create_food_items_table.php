<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('food_items', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete()->comment('NULL = Open Food Facts; Con ID = Alimento custom');
            $table->string('barcode', 60)->unique()->nullable();
            $table->string('name', 200);
            $table->string('brand', 100)->nullable();
            $table->decimal('serving_size_g', 6, 2)->default(100);
            $table->decimal('calories_per_100g', 6, 1);
            $table->decimal('protein_per_100g', 5, 1);
            $table->decimal('carbs_per_100g', 5, 1);
            $table->decimal('fat_per_100g', 5, 1);
            $table->decimal('fiber_per_100g', 5, 1)->default(0);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('food_items');
    }
};
