<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workout_sets', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->foreignUuid('work_exercise_id')->constrained('workout_exercises')->cascadeOnDelete();
            $table->integer('set_order')->comment('Define el numero de set del ejercicio');
            $table->enum('type', ['NORMAL', 'WARNUP', 'DROPSET', 'FAILURE'])->default('NORMAL');
            $table->integer('range_rep_ini');
            $table->integer('range_rep_end');
            $table->decimal('target_rpe', 3, 1)->nullable()->comment('Esfuerzo estimado (ej: 8.5)');
            $table->integer('rest_second')->nullable()->comment('Segundos de descanso especificado para este set');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workout_sets');
    }
};
