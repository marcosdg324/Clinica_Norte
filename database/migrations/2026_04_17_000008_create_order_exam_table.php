<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_exam', function (Blueprint $table) {
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->foreignId('exam_id')->constrained('exams')->onDelete('cascade');

            $table->primary(['order_id', 'exam_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_exam');
    }
};
