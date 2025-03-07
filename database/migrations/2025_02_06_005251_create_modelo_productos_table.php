<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('modelo_productos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained()->onDelete('cascade'); // Relación con el producto
            $table->string('nombre'); // Nombre del modelo
            $table->decimal('precio', 10, 2); // Precio del modelo
            $table->integer('cantidad_minima')->default(1); // Cantidad mínima
            $table->integer('cantidad_maxima')->default(100); // Cantidad máxima
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modelo_productos');
    }
};
