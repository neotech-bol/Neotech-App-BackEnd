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
        Schema::create('caracteristicas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade')->onUpdate('cascade');
            $table->string('caracteristica'); // Nombre de la característica
            $table->string('tipo')->nullable(); // Tipo de característica (opcional)
            $table->string('valor')->nullable(); // Valor de la característica
            $table->integer('orden')->nullable(); // Orden de visualización
            $table->text('descripcion')->nullable(); // Descripción de la característica
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('caracteristicas');
    }
};
