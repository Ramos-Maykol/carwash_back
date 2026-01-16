<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Cliente;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;

class AuthController extends Controller
{
    /**
     * Registra un nuevo cliente.
     */
    public function register(Request $request)
    {
        // 1. Validar los datos que llegan
        // Soportamos 2 formatos de payload:
        // - { nombre, apellido, telefono, ... }
        // - { name, telefono, ... } donde name se divide en nombre/apellido
        $request->validate([
            'nombre' => ['nullable', 'string', 'max:255'],
            'apellido' => ['nullable', 'string', 'max:255'],
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()], // 'confirmed' busca 'password_confirmation'
            'telefono' => ['required', 'string', 'max:20'],
        ]);

        $nombre = $request->input('nombre');
        $apellido = $request->input('apellido');
        $name = $request->input('name');

        // 2. Normalizar nombre/apellido
        if ((!$nombre || !$apellido) && $name) {
            $nameNormalized = trim(preg_replace('/\s+/', ' ', $name));
            $parts = $nameNormalized !== '' ? explode(' ', $nameNormalized, 2) : [];
            $nombre = $nombre ?: ($parts[0] ?? null);
            $apellido = $apellido ?: ($parts[1] ?? null);
        }

        if (!$nombre || !$apellido) {
            return response()->json([
                'message' => 'Nombre y apellido son obligatorios (envía nombre+apellido o name completo).'
            ], 422);
        }

        // 3. Crear el Usuario (para el login)
        $user = User::create([
            'name' => trim($nombre . ' ' . $apellido),
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // 4. Crear el Cliente (para los datos del perfil)
        $cliente = Cliente::create([
            'usuario_id' => $user->id,
            'nombre' => $nombre,
            'apellido' => $apellido,
            'telefono' => $request->telefono,
        ]);
        
        // 4. Asignar el rol de 'cliente' (de Spatie)
        $user->assignRole('cliente');

        // 5. Devolver una respuesta (token de acceso)
        return response()->json([
            'message' => 'Cliente registrado exitosamente',
            'token' => $user->createToken('auth_token')->plainTextToken,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames(),
            ],
        ], 201);
    }

    /**
     * Inicia sesión para un usuario existente.
     */
    public function login(Request $request)
    {
        // 1. Validar los datos
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // 2. Intentar autenticar al usuario
        if (!Auth::attempt($request->only('email', 'password'))) {
            // Si falla, devolver error
            return response()->json([
                'message' => 'Credenciales inválidas'
            ], 401); // 401 No Autorizado
        }

        // 3. Si tiene éxito, obtener el usuario
        $user = User::where('email', $request['email'])->firstOrFail();

        // 4. Crear y devolver el token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Inicio de sesión exitoso',
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames() // Devuelve roles [ej. "admin" o "cliente"]
            ]
        ]);
    }

    /**
     * Cierra la sesión del usuario (elimina el token).
     */
    public function logout(Request $request)
    {
        // Elimina el token que se usó para la autenticación
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Sesión cerrada exitosamente'
        ]);
    }
}
