<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exercise_substitutes', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->foreignUuid('exercise_id')->constrained('exercises')->cascadeOnDelete()->comment('Ejercicio original');
            $table->foreignUuid('substitute_exercise_id')->constrained('exercises')->cascadeOnDelete()->comment('Alternativa si máquina ocupada');
            $table->string('notes', 255)->nullable()->comment('Ej: Si la polea alta está ocupada');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exercise_substitutes');
    }
};
