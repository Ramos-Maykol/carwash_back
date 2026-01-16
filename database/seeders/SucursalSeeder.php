<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Sucursal;

class SucursalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       // 1. Crear la Sucursal
        $sucursal = Sucursal::firstOrCreate(
            ['nombre' => 'Carwash Sede Principal'],
            [
                'direccion' => 'Av. Ejemplo 123, Ciudad',
                'telefono' => '987654321',
            ]
        );

        $this->command->info('Sucursal "Carwash Sede Principal" creada.');
        // 2. Crear los Horarios de Trabajo para esta sucursal
        // (1 = Lunes, 5 = Viernes)
        $diasLaborables = [1, 2, 3, 4, 5]; // Lunes a Viernes
        foreach ($diasLaborables as $dia) {
            $sucursal->horariosTrabajo()->firstOrCreate(
                [
                    'dia_semana' => $dia,
                ],
                [
                    'hora_inicio' => '09:00:00',
                    'hora_fin' => '17:00:00', // 5 PM
                ]
            );
        }

        // Crear horario de Sábado (6 = Sábado)
        $sucursal->horariosTrabajo()->firstOrCreate(
            [
                'dia_semana' => 6,
            ],
            [
                'hora_inicio' => '09:00:00',
                'hora_fin' => '13:00:00', // 1 PM
            ]
        );
        // El Domingo (7) no se crea, por lo tanto, está cerrado.

        $this->command->info('Horarios de Lunes a Sábado creados para la sede principal.');
    }
}
