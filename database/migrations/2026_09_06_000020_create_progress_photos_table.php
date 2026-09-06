<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('progress_photos', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->foreignUuid('measurement_id')->constrained('body_measurement')->cascadeOnDelete();
            $table->enum('pose_type', ['FRONT', 'BACK', 'LEFT', 'RIGHT']);
            $table->string('photo_path', 255);
            $table->timestamp('taken_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('progress_photos');
    }
};
