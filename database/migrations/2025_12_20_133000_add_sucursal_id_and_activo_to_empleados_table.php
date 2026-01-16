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
        Schema::table('empleados', function (Blueprint $table) {
            $table->foreignId('sucursal_id')
                ->nullable()
                ->after('cargo_id')
                ->constrained('sucursals')
                ->nullOnDelete();

            $table->boolean('activo')
                ->default(true)
                ->after('telefono');

            $table->index(['sucursal_id', 'activo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('empleados', function (Blueprint $table) {
            $table->dropIndex(['sucursal_id', 'activo']);
            $table->dropConstrainedForeignId('sucursal_id');
            $table->dropColumn('activo');
        });
    }
};
