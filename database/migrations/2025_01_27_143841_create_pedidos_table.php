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
            $table->foreignId('cupon_id')->nullable()->constrained('cupones')->onDelete('set null'); // Agregando cupon_id
            $table->decimal('total_amount', 10, 2)->nullable();
            $table->decimal('total_to_pay', 10, 2)->nullable();
            $table->decimal('pending', 10, 2)->nullable();
            $table->boolean('estado')->default(false);
            $table->string('payment_method')->nullable(); // Agregando el método de pago
            $table->string('voucher')->nullable(); // Agregando el campo para el comprobante (voucher)
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
