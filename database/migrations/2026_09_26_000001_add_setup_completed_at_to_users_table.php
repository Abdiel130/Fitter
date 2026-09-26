<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('setup_completed_at')->nullable()->after('active_sequence_index')->comment('Momento en que se completó la configuración inicial (5 pasos). NULL = pendiente');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('setup_completed_at');
        });
    }
};
