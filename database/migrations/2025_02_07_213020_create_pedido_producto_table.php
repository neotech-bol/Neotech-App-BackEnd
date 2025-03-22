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
        Schema::create('pedido_producto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->constrained('pedidos')->onDelete('cascade');
            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
            $table->foreignId('modelo_id')->nullable()->constrained('modelo_productos')->onDelete('cascade');
            $table->decimal('precio', 10, 2)->nullable(); // Precio regular al momento de la compra
            $table->decimal('precio_preventa', 10, 2)->nullable(); // Precio de preventa al momento de la compra
            $table->boolean('es_preventa')->default(false); // Indica si la compra fue con precio de preventa
            $table->integer('cantidad')->default(1);
            $table->string('color')->nullable(); // Opcional: si también quieres guardar el color

            // futuro
            $table->decimal('subtotal', 10, 2)->nullable(); // Subtotal para este producto
            $table->decimal('descuento', 10, 2)->nullable(); // Descuento aplicado a este producto
            $table->decimal('impuesto', 10, 2)->nullable(); // Impuesto aplicado a este producto
            $table->text('notas')->nullable(); // Notas sobre el producto
            $table->string('estado_producto')->default('en espera'); // Estado del producto en el pedido
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedido_producto');
    }
};