<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('body_measurement', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('log_date');
            $table->decimal('weight_kg', 5, 2)->comment('Peso en ayunas');
            $table->decimal('body_fat_percentage', 4, 2)->nullable();
            $table->decimal('neck_cm', 5, 2)->nullable();
            $table->decimal('chest_cm', 5, 2)->nullable();
            $table->decimal('waist_navel_cm', 5, 2)->nullable();
            $table->decimal('hips_cm', 5, 2)->nullable();
            $table->decimal('bicep_left_cm', 5, 2)->nullable();
            $table->decimal('bicep_right_cm', 5, 2)->nullable();
            $table->decimal('thigh_left_cm', 5, 2)->nullable();
            $table->decimal('thigh_right_cm', 5, 2)->nullable();
            $table->decimal('calf_cm', 5, 2)->nullable();
            $table->text('notes')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('body_measurement');
    }
};
