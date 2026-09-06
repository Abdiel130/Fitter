<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('body_parts', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique()->comment('chest, back, waist, upper arms...');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('body_parts');
    }
};
