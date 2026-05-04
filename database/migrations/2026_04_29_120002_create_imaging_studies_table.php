<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('imaging_studies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('restrict');
            $table->foreignId('exam_id')->constrained('exams')->onDelete('restrict');
            $table->foreignId('equipment_id')->nullable()->constrained('imaging_equipment')->onDelete('restrict');
            $table->foreignId('bioquimico_id')->nullable()->constrained('users')->onDelete('restrict');
            $table->string('study_code')->unique();
            $table->enum('status', ['programado', 'paciente_presente', 'en_proceso', 'completado', 'cancelado'])->default('programado');
            $table->string('result_file')->nullable();
            $table->text('result_notes')->nullable();
            $table->timestamp('collected_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('study_code');
            $table->index('order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('imaging_studies');
    }
};
