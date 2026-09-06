<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_supplement_logs', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->foreignUuid('daily_habit_log_id')->constrained('daily_habit_logs')->cascadeOnDelete();
            $table->foreignUuid('supplement_id')->constrained('supplements')->cascadeOnDelete();
            $table->boolean('is_taken')->default(false);
            $table->timestamp('taken_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_supplement_logs');
    }
};
