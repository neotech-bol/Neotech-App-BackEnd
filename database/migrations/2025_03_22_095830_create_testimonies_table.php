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
        Schema::create('testimonies', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_completo');
            $table->string('ocupacion');
            $table->text('experiencia');
            $table->integer('calificacion')->check('calificacion >= 1 AND calificacion <= 5'); // Calificación entre 1 y 5
            $table->enum('estado', ['pendiente', 'aprobado', 'rechazado'])->default('pendiente'); // Estado de aprobación del testimonio
            $table->dateTime('fecha_publicacion')->nullable(); // Fecha de publicación del testimonio
            $table->string('imagen')->nullable(); // Ruta de la imagen del testimonio
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null'); // Usuario que envió el testimonio
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('testimonies');
    }
};
