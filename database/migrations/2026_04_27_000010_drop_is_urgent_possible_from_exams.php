<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Elimina is_urgent_possible de la tabla exams.
 * El campo de urgencia se gestiona ahora a nivel de orden, no de examen.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn('is_urgent_possible');
        });
    }

    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->boolean('is_urgent_possible')->default(false)->after('price');
        });
    }
};
