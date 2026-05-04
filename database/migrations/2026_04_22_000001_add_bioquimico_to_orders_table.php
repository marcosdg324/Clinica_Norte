<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('bioquimico_id')
                ->nullable()
                ->after('receptionist_id')
                ->constrained('users')
                ->onDelete('restrict');

            $table->timestamp('accepted_at')
                ->nullable()
                ->after('bioquimico_id');

            $table->index('bioquimico_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['bioquimico_id']);
            $table->dropIndex(['bioquimico_id']);
            $table->dropColumn(['bioquimico_id', 'accepted_at']);
        });
    }
};
