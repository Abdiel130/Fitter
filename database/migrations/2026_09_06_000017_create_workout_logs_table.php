<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workout_logs', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('workout_id')->constrained('workout')->cascadeOnDelete()->comment('Día de la secuencia ejecutado');
            $table->string('title', 100)->comment('Ej: Sesión de Empuje - 15 Oct');
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->integer('overall_rpe')->nullable()->comment('Fatiga general de la sesión (1-10)');
            $table->text('notes')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workout_logs');
    }
};
