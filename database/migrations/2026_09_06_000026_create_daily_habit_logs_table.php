<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_habit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('log_date');
            $table->decimal('sleep_hours', 4, 2)->nullable();
            $table->integer('sleep_quality')->nullable()->comment('Escala 1 al 5');
            $table->integer('energy_level')->nullable()->comment('Escala 1 al 5');
            $table->integer('water_intake_ml')->default(0);
            $table->integer('water_target_ml')->default(3000);

            $table->unique(['user_id', 'log_date'], 'daily_habit_logs_index_1');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_habit_logs');
    }
};
