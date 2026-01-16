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
        Schema::create('precio_servicios', function (Blueprint $table) {
            $table->id();
            // Llave Foránea: Qué servicio es
            $table->foreignId('servicio_id')
                  ->constrained('servicios')
                  ->onDelete('cascade'); // Si se borra el servicio, se borran sus precios

            // Llave Foránea: Para qué tipo de vehículo
            $table->foreignId('tipo_vehiculo_id')
                  ->constrained('tipo_vehiculos')
                  ->onDelete('restrict'); // No borrar tipo si tiene precios asociados

            // El precio
            $table->decimal('precio', 10, 2); // 10 dígitos en total, 2 después del decimal

            // Restricción ÚNICA: No puede haber dos precios para la misma
            // combinación de servicio y tipo de vehículo.
            $table->unique(['servicio_id', 'tipo_vehiculo_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('precio_servicios');
    }
};
