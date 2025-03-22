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
            $table->foreignId('producto_id')->nullable()->constrained()->onDelete('cascade'); // Relación con el producto (ahora nullable)
            $table->string('nombre'); // Nombre del modelo
            $table->decimal('precio', 10, 2); // Precio del modelo
            $table->decimal('precio_preventa', 10, 2)->nullable(); // Precio de preventa
            $table->integer('cantidad_minima')->default(1); // Cantidad mínima
            $table->integer('cantidad_maxima')->default(100); // Cantidad máxima
            $table->integer('cantidad_minima_preventa')->nullable(); // Cantidad mínima para preventa
            $table->integer('cantidad_maxima_preventa')->nullable(); // Cantidad máxima para preventa
            $table->boolean('activo')->default(true); // Estado del modelo
            //futuro
            $table->text('descripcion')->nullable(); // Descripción del modelo
            $table->string('sku')->nullable(); // SKU del modelo
            $table->string('imagen')->nullable(); // Ruta de la imagen del modelo
            $table->dateTime('fecha_disponibilidad')->nullable(); // Fecha de disponibilidad
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