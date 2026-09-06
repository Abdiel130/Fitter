<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exercise_body_parts', function (Blueprint $table) {
            $table->foreignUuid('exercise_id')->constrained('exercises')->cascadeOnDelete();
            $table->foreignId('body_part_id')->constrained('body_parts')->cascadeOnDelete();
            $table->primary(['exercise_id', 'body_part_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exercise_body_parts');
    }
};
