<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Empleado;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class EmpleadoController extends Controller
{
    /**
     * Muestra una lista de todos los empleados.
     */
    public function index()
    {
        // Cargar empleados con sus relaciones de 'usuario' (para email) y 'cargo' (para nombre de cargo)
        $empleados = Empleado::with('usuario', 'cargo', 'sucursal')->get();
        return response()->json($empleados);
    }

    /**
     * Almacena un nuevo empleado (creando su usuario asociado).
     */
    public function store(Request $request)
    {
        $datosValidados = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => ['required', 'confirmed', Password::defaults()],
            'cargo_id' => 'required|integer|exists:cargos,id',
            'sucursal_id' => 'nullable|integer|exists:sucursals,id',
            'activo' => 'sometimes|boolean',
            'codigo_empleado' => 'nullable|string|max:50|unique:empleados,codigo_empleado',
        ]);

        try {
            DB::beginTransaction();

            // 1. Crear el Usuario (para login)
            $user = User::create([
                'name' => $datosValidados['nombre'] . ' ' . $datosValidados['apellido'],
                'email' => $datosValidados['email'],
                'password' => Hash::make($datosValidados['password']),
            ]);

            // 2. Asignar el Rol de Spatie (para permisos)
            // Asumimos que todos los empleados nuevos reciben el rol 'empleado'
            // El rol 'admin' se asigna manualmente
            $user->assignRole('empleado');

            // 3. Crear el Empleado (para perfil)
            $empleado = Empleado::create([
                'usuario_id' => $user->id,
                'cargo_id' => $datosValidados['cargo_id'],
                'sucursal_id' => $datosValidados['sucursal_id'] ?? null,
                'nombre' => $datosValidados['nombre'],
                'apellido' => $datosValidados['apellido'],
                'codigo_empleado' => $datosValidados['codigo_empleado'],
                'activo' => $datosValidados['activo'] ?? true,
            ]);

            DB::commit();
            
            $empleado->load('usuario', 'cargo', 'sucursal'); // Cargar relaciones para la respuesta
            return response()->json($empleado, 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al crear el empleado: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Muestra un empleado específico.
     */
    public function show(Empleado $empleado)
    {
        $empleado->load('usuario', 'cargo', 'sucursal');
        return response()->json($empleado);
    }

    /**
     * Actualiza un empleado y su usuario asociado.
     */
    public function update(Request $request, Empleado $empleado)
    {
        $datosValidados = $request->validate([
            'nombre' => 'required|string|max:255',
            'apellido' => 'required|string|max:255',
            // Asegurarse de que el email sea único, ignorando al usuario actual
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($empleado->usuario_id)],
            // La contraseña es opcional en la actualización
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'cargo_id' => 'required|integer|exists:cargos,id',
            'sucursal_id' => 'nullable|integer|exists:sucursals,id',
            'activo' => 'sometimes|boolean',
            'codigo_empleado' => ['nullable', 'string', 'max:50', Rule::unique('empleados', 'codigo_empleado')->ignore($empleado->id)],
        ]);
        
        try {
            DB::beginTransaction();
            
            $user = $empleado->usuario;

            // 1. Actualizar el Usuario
            $userData = [
                'name' => $datosValidados['nombre'] . ' ' . $datosValidados['apellido'],
                'email' => $datosValidados['email'],
            ];
            // Solo actualizar la contraseña SI se envió una nueva
            if (!empty($datosValidados['password'])) {
                $userData['password'] = Hash::make($datosValidados['password']);
            }
            $user->update($userData);

            // 2. Actualizar el Empleado
            $empleado->update([
                'cargo_id' => $datosValidados['cargo_id'],
                'sucursal_id' => $datosValidados['sucursal_id'] ?? null,
                'nombre' => $datosValidados['nombre'],
                'apellido' => $datosValidados['apellido'],
                'codigo_empleado' => $datosValidados['codigo_empleado'],
                'activo' => $datosValidados['activo'] ?? $empleado->activo,
            ]);

            DB::commit();

            $empleado->load('usuario', 'cargo', 'sucursal');
            return response()->json($empleado);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al actualizar el empleado: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Elimina un empleado y su usuario asociado.
     */
    public function destroy(Empleado $empleado)
    {
        try {
            DB::beginTransaction();

            $user = $empleado->usuario;

            // 1. Eliminar el Empleado
            $empleado->delete();

            // 2. Eliminar el Usuario (esto eliminará roles, tokens, etc.)
            // NOTA: Asegúrate de que tu FK en 'empleados' tenga 'onDelete('cascade')'
            // o hazlo manualmente como aquí.
            if ($user) {
                $user->delete();
            }

            DB::commit();
            
            return response()->json(null, 204); // 204 Sin Contenido

        } catch (\Exception $e) {
            DB::rollBack();
            // 409 Conflicto (si, por ejemplo, el empleado tiene reservas asignadas)
            return response()->json(['message' => 'Error al eliminar el empleado. ¿Tiene reservas asignadas? ' . $e->getMessage()], 409);
        }
    }
}
