<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exercise_equipments', function (Blueprint $table) {
            $table->foreignUuid('exercise_id')->constrained('exercises')->cascadeOnDelete();
            $table->foreignId('equipment_id')->constrained('equipments')->cascadeOnDelete();
            $table->primary(['exercise_id', 'equipment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exercise_equipments');
    }
};
