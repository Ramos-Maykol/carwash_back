<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // (Opcional) Crear algunos usuarios de prueba (clientes)
        // \App\Models\User::factory(10)->create();

        // Llamamos a nuestros seeders personalizados EN ORDEN
        // CORRECCIÓN: Se añade ->command antes de ->info()
        $this->command->info('Ejecutando Seeder de Roles y Permisos...');
        $this->call(RolesAndPermissionsSeeder::class);

        $this->command->info('Ejecutando Seeder de Usuario Admin...');
        $this->call(AdminUserSeeder::class);

        $this->command->info('Ejecutando Seeder de Sucursal...');
        $this->call(SucursalSeeder::class);
        
        $this->command->info('Ejecutando Seeder de Catálogo...');
        $this->call(CatalogoSeeder::class);
    }
}
