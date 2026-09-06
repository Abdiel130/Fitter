<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplements', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name', 100)->comment('Ej: Creatina Monohidratada, Omega 3');
            $table->string('target_dosage', 50)->nullable()->comment('Ej: 5g, 2 cápsulas');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplements');
    }
};
