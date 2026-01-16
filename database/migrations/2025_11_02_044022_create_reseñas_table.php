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
        Schema::create('reseñas', function (Blueprint $table) {
            $table->id();
            // Llave Foránea: Qué reserva se está reseñando
            $table->foreignId('reserva_id')
                  ->constrained('reservas')
                  ->onDelete('cascade'); // Si se borra la reserva, la reseña no importa

            // Llave Foránea: Qué cliente está dejando la reseña
            $table->foreignId('cliente_id')
                  ->constrained('clientes')
                  ->onDelete('cascade'); // Si se borra el cliente, se borran sus reseñas

            // Calificación (ej. 1, 2, 3, 4, 5)
            $table->unsignedTinyInteger('calificacion');
            
            $table->text('comentario')->nullable(); // Comentario opcional

            // Para moderación
            $table->boolean('esta_aprobada')->default(true);

            // Regla de Negocio: Un cliente solo puede reseñar una reserva UNA vez.
            $table->unique(['reserva_id', 'cliente_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reseñas');
    }
};
