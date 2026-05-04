<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('imaging_equipment', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['ecógrafo', 'rayos_x', 'tomógrafo', 'otro']);
            $table->text('description')->nullable();
            $table->enum('status', ['disponible', 'mantenimiento', 'fuera_de_servicio'])->default('disponible');
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('imaging_equipment');
    }
};
