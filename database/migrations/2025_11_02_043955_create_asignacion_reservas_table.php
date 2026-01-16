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
        Schema::create('asignacion_reservas', function (Blueprint $table) {
            $table->id();
            // Llave Foránea: Qué reserva
            $table->foreignId('reserva_id')
                  ->constrained('reservas')
                  ->onDelete('cascade'); // Si se borra la reserva, se borra la asignación

            // Llave Foránea: Qué empleado
            $table->foreignId('empleado_id')
                  ->constrained('empleados')
                  ->onDelete('cascade'); // Si se borra el empleado, se borra la asignación

            // Único para que un empleado no sea asignado dos veces al mismo trabajo
            $table->unique(['reserva_id', 'empleado_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asignacion_reservas');
    }
};
