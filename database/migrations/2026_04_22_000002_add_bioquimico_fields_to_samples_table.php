<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('samples', function (Blueprint $table) {
            $table->foreignId('bioquimico_asignado_id')
                ->nullable()
                ->after('collected_by')
                ->constrained('users')
                ->nullOnDelete();

            $table->text('motivo_rechazo')
                ->nullable()
                ->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('samples', function (Blueprint $table) {
            $table->dropForeign(['bioquimico_asignado_id']);
            $table->dropColumn(['bioquimico_asignado_id', 'motivo_rechazo']);
        });
    }
};
