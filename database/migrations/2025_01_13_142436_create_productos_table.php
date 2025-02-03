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
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('catalogo_id')->nullable()->constrained('catalogos')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('categoria_id')->nullable()->constrained('categorias')->onDelete('cascade')->onUpdate('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade')->onUpdate('cascade');
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->decimal('precio', 10,2);
            $table->boolean('estado')->default(true);
            $table->integer('cantidad')->default(0); // Campo para la cantidad
            $table->string('imagen_principal')->nullable(); // Campo para la imagen principal
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
