<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('samples', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->onDelete('restrict');
            $table->foreignId('exam_id')->constrained('exams')->onDelete('restrict');
            $table->string('barcode')->unique();
            $table->enum('status', ['recibida', 'en_analisis', 'procesada', 'rechazada'])->default('recibida');
            $table->timestamp('collected_at')->nullable();
            $table->foreignId('collected_by')->constrained('users')->onDelete('restrict');
            $table->string('location')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('barcode');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('samples');
    }
};
