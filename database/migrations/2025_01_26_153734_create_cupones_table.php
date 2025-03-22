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
        Schema::create('cupones', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique(); // Código del cupón
            $table->decimal('descuento', 10, 2); // Monto del descuento
            $table->string('tipo'); // Tipo de descuento: 'porcentaje' o 'fijo'
            $table->dateTime('fecha_inicio'); // Fecha de inicio de validez
            $table->dateTime('fecha_fin'); // Fecha de fin de validez
            $table->boolean('activo')->default(true); // Estado del cupón
            $table->boolean('usado')->nullable()->default(false); // Estado del uso del cupón
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cupones');
    }
};
