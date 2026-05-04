<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Revierte el enfoque de requisitos por categoría y vuelve al enfoque
 * original: requisitos vinculados directamente a cada examen (exam_id).
 *
 * 1. Elimina los registros de requisitos que sólo tenían exam_category_id
 *    (exam_id NULL) — fueron cargados por CatalogCompletionSeeder.
 * 2. Elimina la FK y la columna exam_category_id.
 * 3. Restaura exam_id como NOT NULL.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Eliminar registros que no tienen exam_id (eran de categoría)
        DB::table('exam_requirements')->whereNull('exam_id')->delete();

        Schema::table('exam_requirements', function (Blueprint $table) {
            // 2. Eliminar FK y columna exam_category_id
            $table->dropForeign(['exam_category_id']);
            $table->dropColumn('exam_category_id');

            // 3. Volver exam_id a NOT NULL
            $table->foreignId('exam_id')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('exam_requirements', function (Blueprint $table) {
            $table->foreignId('exam_id')->nullable()->change();

            $table->foreignId('exam_category_id')
                ->nullable()
                ->after('exam_id')
                ->constrained('exam_categories')
                ->onDelete('cascade');
        });
    }
};
