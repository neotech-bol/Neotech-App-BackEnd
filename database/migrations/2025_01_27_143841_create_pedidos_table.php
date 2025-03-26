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
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('cupon_id')->nullable()->constrained('cupones')->onDelete('set null');
            $table->decimal('total_amount', 10, 2)->nullable();
            $table->decimal('total_to_pay', 10, 2)->nullable();
            $table->decimal('pending', 10, 2)->nullable();
            $table->string('estado')->default(false); // Estado del pedido
            $table->string('payment_method')->nullable(); // Método de pago
            $table->string('voucher')->nullable(); // Comprobante (voucher)
            
            // Campos adicionales
            $table->string('direccion_envio')->nullable(); // Dirección de envío
            $table->string('ciudad')->nullable(); // Ciudad
            $table->string('codigo_postal')->nullable(); // Código postal
            $table->string('pais')->nullable(); // País
            $table->dateTime('fecha_envio')->nullable(); // Fecha de envío
            $table->dateTime('fecha_entrega')->nullable(); // Fecha de entrega
            $table->text('notas')->nullable(); // Notas del pedido
            $table->string('metodo_envio')->nullable(); // Método de envío
            $table->decimal('descuento_aplicado', 10, 2)->nullable(); // Descuento aplicado al pedido
        
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
