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
        Schema::create('contactanos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_completo');
            $table->string('telefono');
            $table->string('correo');
            $table->text('mensaje');
            $table->string('departamento');
            $table->boolean('estado')->default(false); // Estado de la consulta
            //a futuro
            $table->boolean('correo_valido')->default(true); // Indica si el correo es válido
            $table->dateTime('fecha_envio')->nullable(); // Fecha y hora de envío
            $table->string('ip_address')->nullable(); // Dirección IP del usuario
            $table->string('metodo_contacto')->nullable(); // Método de contacto
            $table->boolean('respondido')->default(false); // Indica si el mensaje ha sido respondido
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contactanos');
    }
};
