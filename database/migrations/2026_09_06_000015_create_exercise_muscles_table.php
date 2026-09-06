<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exercise_muscles', function (Blueprint $table) {
            $table->foreignUuid('exercise_id')->constrained('exercises')->cascadeOnDelete();
            $table->foreignId('muscle_id')->constrained('muscles')->cascadeOnDelete();
            $table->boolean('is_target')->default(true)->comment('true: targetMuscle, false: secondaryMuscle');
            $table->primary(['exercise_id', 'muscle_id', 'is_target']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exercise_muscles');
    }
};
