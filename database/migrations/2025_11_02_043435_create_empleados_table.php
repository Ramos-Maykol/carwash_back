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
        Schema::create('empleados', function (Blueprint $table) {
            $table->id();
            // Llave Foránea para la autenticación (tabla 'users')
            $table->foreignId('usuario_id')
                  ->constrained('users')
                  ->onDelete('cascade'); // Si se borra el usuario, se borra el perfil de empleado

            // Llave Foránea para el puesto (tabla 'cargos')
            $table->foreignId('cargo_id')
                  ->constrained('cargos')
                  ->onDelete('restrict'); // Evita borrar un cargo si hay empleados con él

            $table->string('nombre', 100);
            $table->string('apellido', 100);
            $table->string('codigo_empleado', 20)->unique()->nullable();
            $table->date('fecha_contratacion')->nullable();
            $table->string('telefono', 20)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empleados');
    }
};
