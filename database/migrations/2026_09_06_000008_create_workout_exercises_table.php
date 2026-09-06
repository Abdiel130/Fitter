<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workout_exercises', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->foreignUuid('workout_id')->constrained('workout')->cascadeOnDelete();
            $table->foreignUuid('exercise_id')->constrained('exercises')->cascadeOnDelete();
            $table->integer('order_in_routine')->comment('1º Press banca, 2º Fondos...');
            $table->integer('rest_seconds')->default(90)->comment('Segundos para el timer de descanso GLOBAL');
            $table->text('notes')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workout_exercises');
    }
};
