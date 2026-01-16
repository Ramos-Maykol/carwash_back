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
        Schema::create('horario_trabajos', function (Blueprint $table) {
            $table->id();
            // Llave Foránea: A qué sucursal pertenece este horario
            $table->foreignId('sucursal_id')
                  ->constrained('sucursals')
                  ->onDelete('cascade');

            // 0=Domingo, 1=Lunes, 2=Martes, 3=Miércoles, 4=Jueves, 5=Viernes, 6=Sábado
            $table->unsignedTinyInteger('dia_semana');
            
            $table->time('hora_inicio'); // Ej: 09:00:00
            $table->time('hora_fin');    // Ej: 18:00:00

            // Restricción para que no se repita el día para la misma sucursal
            $table->unique(['sucursal_id', 'dia_semana']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('horario_trabajos');
    }
};
