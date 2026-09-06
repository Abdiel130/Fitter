<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('routine_workouts', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->foreignUuid('routine_id')->constrained('routine')->cascadeOnDelete();
            $table->foreignUuid('workout_id')->constrained('workout')->cascadeOnDelete();
            $table->integer('order_index')->comment('Orden rotativo de ejecución cíclica');
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['routine_id', 'order_index'], 'routine_workouts_index_0');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('routine_workouts');
    }
};
