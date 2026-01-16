<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Empleado;
use App\Models\Cargo;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Crear el Cargo
        $cargo = Cargo::firstOrCreate(
            ['nombre_cargo' => 'Gerente General'],
            ['descripcion' => 'Administrador principal del sistema']
        );

        // Cargos operativos / de prueba (capacity planning)
        Cargo::firstOrCreate(
            ['nombre_cargo' => 'Lavador'],
            ['descripcion' => 'Personal operativo de lavado']
        );
        Cargo::firstOrCreate(
            ['nombre_cargo' => 'Recepcionista'],
            ['descripcion' => 'Atención al cliente y recepción']
        );

        // 2. Crear el Usuario (para login)
        $adminEmail = 'admin@test.com';
        $adminPass = 'password'; // Puedes cambiar esto

        $usuario = User::firstOrCreate(
            ['email' => $adminEmail],
            [
                'name' => 'Administrador',
                'password' => Hash::make($adminPass),
            ]
        );

        // 3. Crear el Perfil de Empleado
        Empleado::firstOrCreate(
            ['usuario_id' => $usuario->id],
            [
                'nombre' => 'Admin',
                'apellido' => 'Sistema',
                'codigo_empleado' => 'A001',
                'cargo_id' => $cargo->id,
            ]
        );

        // 4. Asignar el Rol de 'admin' (de Spatie)
        $usuario->assignRole('admin');

        // Imprimir las credenciales en la consola
        $this->command->info('Usuario Administrador creado:');
        $this->command->info("Email: $adminEmail");
        $this->command->info("Password: $adminPass");
    }
}
