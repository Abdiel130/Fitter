<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workout_log_sets', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->foreignUuid('workout_log_id')->constrained('workout_logs')->cascadeOnDelete();
            $table->foreignUuid('exercise_id')->constrained('exercises')->cascadeOnDelete();
            $table->integer('set_order')->comment('Serie 1, 2, 3...');
            $table->enum('set_type', ['NORMAL', 'WARNUP', 'DROPSET', 'FAILURE'])->default('NORMAL');
            $table->decimal('weight_input', 6, 2)->comment('Valor exacto introducido (ej: 45.0)');
            $table->enum('weight_unit', ['KG', 'LBS'])->default('KG');
            $table->decimal('weight_kg', 6, 2)->comment('Valor normalizado a kg para cálculos');
            $table->integer('reps_completed');
            $table->decimal('rpe', 3, 1)->nullable();
            $table->decimal('calculated_1rm', 6, 2)->nullable();
            $table->boolean('is_personal_record')->default(false);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workout_log_sets');
    }
};
