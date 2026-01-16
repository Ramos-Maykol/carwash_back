<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\TipoVehiculo;
use App\Models\Servicio;
use App\Models\PrecioServicio;

class CatalogoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Crear Tipos de Vehículo
        $sedan = TipoVehiculo::firstOrCreate(['nombre' => 'Sedan / Auto']);
        $suv = TipoVehiculo::firstOrCreate(['nombre' => 'SUV / Camioneta']);
        $moto = TipoVehiculo::firstOrCreate(['nombre' => 'Moto']);

        // 2. Crear Servicios
        $lavadoBasico = Servicio::firstOrCreate(
            ['nombre' => 'Lavado Básico Exterior'],
            [
                'descripcion' => 'Lavado de carrocería, llantas y secado.',
                'duracion_estimada_minutos' => 30,
            ]
        );
        
        $lavadoCompleto = Servicio::firstOrCreate(
            ['nombre' => 'Lavado Completo (Full)'],
            [
                'descripcion' => 'Lavado básico + aspirado interior y limpieza de tablero.',
                'duracion_estimada_minutos' => 60,
            ]
        );

        $encerado = Servicio::firstOrCreate(
            ['nombre' => 'Encerado'],
            [
                'descripcion' => 'Encerado manual para protección y brillo.',
                'duracion_estimada_minutos' => 45,
            ]
        );

        // 3. Crear los Precios (la conexión)
        
        // Precios Lavado Básico
        PrecioServicio::firstOrCreate(
            ['servicio_id' => $lavadoBasico->id, 'tipo_vehiculo_id' => $sedan->id],
            ['precio' => 30.00]
        );
        PrecioServicio::firstOrCreate(
            ['servicio_id' => $lavadoBasico->id, 'tipo_vehiculo_id' => $suv->id],
            ['precio' => 40.00]
        );
        PrecioServicio::firstOrCreate(
            ['servicio_id' => $lavadoBasico->id, 'tipo_vehiculo_id' => $moto->id],
            ['precio' => 20.00]
        );

        // Precios Lavado Completo
        PrecioServicio::firstOrCreate(
            ['servicio_id' => $lavadoCompleto->id, 'tipo_vehiculo_id' => $sedan->id],
            ['precio' => 50.00]
        );
        PrecioServicio::firstOrCreate(
            ['servicio_id' => $lavadoCompleto->id, 'tipo_vehiculo_id' => $suv->id],
            ['precio' => 65.00]
        );
        // (No ofrecemos lavado completo de moto en este ejemplo)

        // Precios Encerado
        PrecioServicio::firstOrCreate(
            ['servicio_id' => $encerado->id, 'tipo_vehiculo_id' => $sedan->id],
            ['precio' => 45.00]
        );
        PrecioServicio::firstOrCreate(
            ['servicio_id' => $encerado->id, 'tipo_vehiculo_id' => $suv->id],
            ['precio' => 55.00]
        );

        $this->command->info('Catálogo de servicios y precios creado.');
    }
}
