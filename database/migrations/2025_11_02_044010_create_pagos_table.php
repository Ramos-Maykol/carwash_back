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
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            // Llave Foránea: A qué reserva pertenece este pago
            $table->foreignId('reserva_id')
                  ->constrained('reservas')
                  ->onDelete('restrict'); // No borrar una reserva si tiene pagos

            $table->decimal('monto', 10, 2); // Debe coincidir con el precio_final de la reserva
            
            // Método de pago: 'tarjeta_credito', 'efectivo', 'billetera_app', 'transferencia'
            $table->string('metodo_pago', 50);

            // Estado del pago: 'pendiente', 'completado', 'fallido', 'reembolsado'
            $table->string('estado', 30)->default('pendiente');

            // ID de la transacción del proveedor de pagos (Stripe, PayPal, Culqi, etc.)
            // Es 'nullable' por si el pago es en 'efectivo'.
            $table->string('id_transaccion_gateway', 191)->nullable()->unique();
            
            $table->timestamp('fecha_pago')->nullable(); // Cuándo se completó el pago
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
