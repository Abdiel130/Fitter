<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exercises', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete()->comment('NULL = Catálogo precargado; Con ID = Ejercicio propio');
            $table->string('external_id', 50)->unique()->nullable()->comment('exerciseId del JSON de ExerciseDB (01qpYSe)');
            $table->string('name', 150);
            $table->string('gif_url', 255)->nullable()->comment('Path local descargado o URL remota');
            $table->jsonb('instructions')->nullable()->comment('Array de pasos ["Step:1 ...", "Step:2 ..."]');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exercises');
    }
};
