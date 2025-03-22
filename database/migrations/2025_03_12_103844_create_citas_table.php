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
        Schema::create('citas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_completo');
            $table->string('telefono');
            $table->string('correo');
            $table->date('fecha_de_cita');
            $table->time('hora_de_cita');
            $table->string('servicio_solicitado');
            $table->text('mensaje');
            $table->string('departamento');
            $table->boolean('estado')->default(false);
            $table->string('ip_address')->nullable(); // Dirección IP del usuario
            $table->string('metodo_solicitud')->nullable(); // Método de solicitud de la cita
            $table->integer('duracion')->nullable(); // Duración de la cita en minutos
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null'); // Usuario que realizó la cita
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('citas');
    }
};
