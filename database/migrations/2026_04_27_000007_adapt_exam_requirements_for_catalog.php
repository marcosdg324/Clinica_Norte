<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adapta la tabla exam_requirements existente (usada por Orders)
 * añadiendo soporte para el módulo Catalog:
 *  - exam_category_id: FK nullable a exam_categories (para requisitos por categoría)
 *  - instructions: texto adicional con indicaciones al paciente
 *
 * La columna exam_id original no se toca para no romper el dominio Orders.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_requirements', function (Blueprint $table) {
            $table->foreignId('exam_category_id')
                ->nullable()
                ->after('exam_id')
                ->constrained('exam_categories')
                ->onDelete('cascade');

            $table->text('instructions')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('exam_requirements', function (Blueprint $table) {
            $table->dropForeign(['exam_category_id']);
            $table->dropColumn(['exam_category_id', 'instructions']);
        });
    }
};
