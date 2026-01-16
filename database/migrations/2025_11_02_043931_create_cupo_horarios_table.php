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
        Schema::create('cupo_horarios', function (Blueprint $table) {
            $table->id();
            // Llave Foránea: A qué sucursal pertenece el cupo
            $table->foreignId('sucursal_id')
                  ->constrained('sucursals')
                  ->onDelete('cascade');

            // Fecha y hora completas del inicio y fin del cupo
            $table->dateTime('hora_inicio'); // Ej: 2025-11-10 09:00:00
            $table->dateTime('hora_fin');    // Ej: 2025-11-10 09:30:00

            // Estado del cupo: 'disponible', 'reservado', 'bloqueado'
            $table->string('estado', 20)->default('disponible');

            // Índices para que la base de datos busque cupos más rápido
            $table->index(['sucursal_id', 'hora_inicio', 'estado']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cupo_horarios');
    }
};
