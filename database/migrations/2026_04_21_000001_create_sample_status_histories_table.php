<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sample_status_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sample_id')->constrained('samples')->onDelete('cascade');
            $table->string('old_status')->nullable();
            $table->string('new_status');
            $table->foreignId('changed_by')->constrained('users')->onDelete('restrict');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('sample_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sample_status_histories');
    }
};
