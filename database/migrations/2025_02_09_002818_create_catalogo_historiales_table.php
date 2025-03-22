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
        Schema::create('catalogo_historiales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('catalogo_id')->constrained()->onDelete('cascade');
            $table->string('nombre');
            $table->string('descripcion')->nullable();
            $table->string('banner')->nullable();
            $table->integer('orden')->default(0);
            $table->boolean('estado')->default(true);

            //a futuro
            $table->dateTime('fecha_inicio')->nullable(); // Fecha de inicio de validez
            $table->dateTime('fecha_fin')->nullable(); // Fecha de fin de validez
            $table->string('tipo')->nullable(); // Tipo de catálogo (opcional)
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null'); // Usuario que realizó el cambio
            $table->boolean('publicado')->default(false); // Indica si el catálogo está publicado
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalogo_historiales');
    }
};
