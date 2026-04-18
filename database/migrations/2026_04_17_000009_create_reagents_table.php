<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reagents', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('unit');
            $table->integer('stock_quantity')->default(0);
            $table->integer('min_stock')->default(10);
            $table->date('expiration_date')->nullable();
            $table->foreignId('provider_id')->constrained('providers')->onDelete('restrict');
            $table->timestamps();
            $table->softDeletes();

            $table->index('stock_quantity');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reagents');
    }
};
