<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('muscles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80)->unique()->comment('latissimus dorsi, biceps, quadriceps...');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('muscles');
    }
};
