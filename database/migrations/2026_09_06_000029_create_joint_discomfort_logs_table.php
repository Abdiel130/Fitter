<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('joint_discomfort_logs', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->foreignUuid('daily_habit_log_id')->constrained('daily_habit_logs')->cascadeOnDelete();
            $table->string('joint_area', 50)->comment('shoulder_left, knee_right, lumbar...');
            $table->integer('pain_intensity')->comment('Escala 1 al 10');
            $table->string('notes', 255)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('joint_discomfort_logs');
    }
};
