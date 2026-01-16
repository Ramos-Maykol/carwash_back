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
        Schema::table('cupo_horarios', function (Blueprint $table) {
            $table->foreignId('empleado_id')
                ->nullable()
                ->after('sucursal_id')
                ->constrained('empleados')
                ->nullOnDelete();
            $table->unique(['sucursal_id', 'hora_inicio', 'empleado_id'], 'cupo_horarios_slot_empleado_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cupo_horarios', function (Blueprint $table) {
            $table->dropUnique('cupo_horarios_slot_empleado_unique');
            $table->dropConstrainedForeignId('empleado_id');
        });
    }
};
