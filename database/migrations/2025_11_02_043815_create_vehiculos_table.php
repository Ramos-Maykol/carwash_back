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
        Schema::create('vehiculos', function (Blueprint $table) {
            $table->id();
            // Llave Foránea: Quién es el dueño
            $table->foreignId('cliente_id')
                  ->constrained('clientes')
                  ->onDelete('cascade'); // Si se borra el cliente, se borran sus autos

            // Llave Foránea: Qué tipo de auto es
            $table->foreignId('tipo_vehiculo_id')
                  ->constrained('tipo_vehiculos')
                  ->onDelete('restrict'); // No permitir borrar un tipo si hay autos de ese tipo

            $table->string('placa', 20)->unique()->nullable(); // 'unique' para evitar duplicados
            $table->string('marca', 100);
            $table->string('modelo', 100);
            $table->integer('año')->nullable();
            $table->string('color', 50)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehiculos');
    }
};
