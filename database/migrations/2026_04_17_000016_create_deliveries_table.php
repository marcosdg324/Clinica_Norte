<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('result_id')->constrained('results')->onDelete('restrict');
            $table->foreignId('patient_id')->constrained('patients')->onDelete('restrict');
            $table->enum('channel', ['email', 'whatsapp']);
            $table->timestamp('sent_at');
            $table->enum('status', ['enviado', 'fallido'])->default('enviado');
            $table->string('pdf_path')->nullable();
            $table->timestamps();

            $table->index(['result_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};
