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
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade')->onUpdate('cascade');
            $table->integer('rating')->check('rating >= 1 AND rating <= 5'); // Calificación entre 1 y 5
            $table->text('comment')->nullable(); // Comentario opcional
            $table->enum('estado', ['pendiente', 'aprobada', 'rechazada'])->default('pendiente'); // Estado de la calificación
            $table->string('ip_address')->nullable(); // Dirección IP del usuario
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};
