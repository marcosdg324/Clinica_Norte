<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sample_id')->unique()->constrained('samples')->onDelete('restrict');
            $table->foreignId('order_id')->constrained('orders')->onDelete('restrict');
            $table->foreignId('exam_id')->constrained('exams')->onDelete('restrict');
            $table->foreignId('bioquimico_id')->constrained('users')->onDelete('restrict');
            $table->timestamp('validated_at')->nullable();
            $table->boolean('is_critical')->default(false);
            $table->string('pdf_path')->nullable();
            $table->timestamps();

            $table->index('is_critical');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('results');
    }
};
