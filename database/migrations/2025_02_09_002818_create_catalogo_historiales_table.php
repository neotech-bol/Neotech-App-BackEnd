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
