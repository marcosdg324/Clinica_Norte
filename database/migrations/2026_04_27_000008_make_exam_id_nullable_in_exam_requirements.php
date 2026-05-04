<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Hace nullable la columna exam_id en exam_requirements
 * para permitir requisitos vinculados a exam_category_id
 * (módulo Catalog) sin requerir un exam_id del módulo Orders.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_requirements', function (Blueprint $table) {
            $table->foreignId('exam_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('exam_requirements', function (Blueprint $table) {
            $table->foreignId('exam_id')->nullable(false)->change();
        });
    }
};
