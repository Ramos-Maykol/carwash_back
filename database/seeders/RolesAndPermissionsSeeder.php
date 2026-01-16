<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Limpiar la caché de permisos
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // --- Crear Permisos Básicos ---
        // (En un proyecto real, serías más específico)
        Permission::firstOrCreate(['name' => 'ver_dashboard_admin']);
        Permission::firstOrCreate(['name' => 'gestionar_sucursales']);
        Permission::firstOrCreate(['name' => 'gestionar_servicios']);
        Permission::firstOrCreate(['name' => 'gestionar_reservas']);
        Permission::firstOrCreate(['name' => 'gestionar_clientes']);
        Permission::firstOrCreate(['name' => 'ver_reservas_propias']);
        Permission::firstOrCreate(['name' => 'crear_reservas_propias']);

        // --- Crear Roles ---
        $roleAdmin = Role::firstOrCreate(['name' => 'admin']);
        // El rol 'admin' obtiene todos los permisos
        // (En lugar de dárselos uno por uno, le damos un "super-permiso")
        $roleAdmin->givePermissionTo(Permission::all());

        $roleEmpleado = Role::firstOrCreate(['name' => 'empleado']);
        $roleEmpleado->givePermissionTo([
            'ver_dashboard_admin',
            'gestionar_reservas',
        ]);

        $roleCliente = Role::firstOrCreate(['name' => 'cliente']);
        $roleCliente->givePermissionTo([
            'ver_reservas_propias',
            'crear_reservas_propias',
        ]);
    }
}
