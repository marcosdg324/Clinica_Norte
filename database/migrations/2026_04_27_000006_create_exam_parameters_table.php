<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_parameters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_category_id')->constrained('exam_categories')->onDelete('cascade');
            $table->string('name');
            $table->string('unit')->nullable();
            $table->decimal('reference_min', 10, 4)->nullable();
            $table->decimal('reference_max', 10, 4)->nullable();
            $table->decimal('critical_min', 10, 4)->nullable();
            $table->decimal('critical_max', 10, 4)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('exam_category_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_parameters');
    }
};
