<?php

use App\Domains\Catalog\Models\ExamCategory;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Vincula la tabla exams (Orders) con exam_categories (Catalog).
 * El campo es nullable para no romper exámenes existentes.
 * La restricción onDelete('set null') evita que borrar una categoría
 * rompa los exámenes que la referencian.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->foreignId('exam_category_id')
                ->nullable()
                ->after('name')
                ->constrained('exam_categories')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropForeignIdFor(ExamCategory::class);
            $table->dropColumn('exam_category_id');
        });
    }
};
