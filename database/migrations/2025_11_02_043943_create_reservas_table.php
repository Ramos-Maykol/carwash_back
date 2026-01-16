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
        Schema::create('reservas', function (Blueprint $table) {
            $table->id();
            // Llave Foránea: Quién está reservando
            $table->foreignId('cliente_id')
                  ->constrained('clientes')
                  ->onDelete('cascade');

            // Llave Foránea: Cuándo está reservando
            // ¡ÚNICA! Un cupo solo puede ser reservado una vez.
            // Esto previene "double-booking" a nivel de base de datos.
            $table->foreignId('cupo_horario_id')
                  ->unique()
                  ->constrained('cupo_horarios')
                  ->onDelete('restrict'); // No borrar un cupo si tiene una reserva

            // Llave Foránea: Qué vehículo se va a lavar
            $table->foreignId('vehiculo_id')
                  ->constrained('vehiculos')
                  ->onDelete('restrict'); // No borrar un vehículo si tiene reservas

            // Llave Foránea: Qué servicio/precio se contrató
            // Esto nos permite saber qué se vendió.
            $table->foreignId('precios_servicios_id')
                  ->constrained('precio_servicios')
                  ->onDelete('restrict');

            // Precio final guardado en el momento de la reserva.
            // Es CRÍTICO para que, si los precios cambian,
            // la reserva mantenga el precio con el que se hizo.
            $table->decimal('precio_final', 10, 2);

            // Estado de la reserva: pendiente, confirmada, en_proceso, completada, cancelada
            $table->string('estado', 30)->default('pendiente');
            
            $table->text('comentarios_cliente')->nullable(); // Notas del cliente
            $table->text('notas_empleado')->nullable(); // Notas internas

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservas');
    }
};
