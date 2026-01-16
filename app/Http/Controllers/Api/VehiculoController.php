<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vehiculo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VehiculoController extends Controller
{
    /**
     * Muestra la lista de vehículos del cliente autenticado.
     */
    public function index(Request $request)
    {
        $cliente = $request->user()->cliente;
        if (!$cliente) {
            return response()->json(['message' => 'Perfil de cliente no encontrado.'], 404);
        }

        // Devolvemos los vehículos del cliente, cargando la info de su 'tipoVehiculo'
        $vehiculos = $cliente->vehiculos()->with('tipoVehiculo')->get();
        return response()->json($vehiculos);
    }

    public function store(Request $request)
    {
        $cliente = $request->user()->cliente;
        if (!$cliente) {
            return response()->json(['message' => 'Perfil de cliente no encontrado.'], 404);
        }

        $datosValidados = $request->validate([
            'marca' => 'required|string|max:100',
            'modelo' => 'required|string|max:100',
            'placa' => ['required', 'string', 'max:20', Rule::unique('vehiculos', 'placa')],
            'color' => 'nullable|string|max:50',
            'tipo_vehiculo_id' => 'required|integer|exists:tipo_vehiculos,id',
        ]);

        $datosValidados['cliente_id'] = $cliente->id;

        $vehiculo = Vehiculo::create($datosValidados);

        $vehiculo->load('tipoVehiculo');

        return response()->json($vehiculo, 201);
    }

    /**
     * Muestra un vehículo específico (si pertenece al cliente).
     */
    public function show(Request $request, Vehiculo $vehiculo)
    {
        // Política de Seguridad: ¿Este vehículo pertenece al cliente?
        if ($vehiculo->cliente_id !== $request->user()->cliente?->id) {
            return response()->json(['message' => 'No autorizado'], 403); // 403 Prohibido
        }

        $vehiculo->load('tipoVehiculo');
        return response()->json($vehiculo);
    }

    /**
     * Actualiza un vehículo específico (si pertenece al cliente).
     */
    public function update(Request $request, Vehiculo $vehiculo)
    {
        // Política de Seguridad
        if ($vehiculo->cliente_id !== $request->user()->cliente?->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $datosValidados = $request->validate([
            'marca' => 'sometimes|required|string|max:100',
            'modelo' => 'sometimes|required|string|max:100',
            // La placa debe ser única, ignorando el vehículo actual
            'placa' => ['sometimes', 'required', 'string', 'max:20', Rule::unique('vehiculos', 'placa')->ignore($vehiculo->id)],
            'color' => 'nullable|string|max:50',
            'tipo_vehiculo_id' => 'sometimes|required|integer|exists:tipo_vehiculos,id',
        ]);

        $vehiculo->update($datosValidados);

        $vehiculo->load('tipoVehiculo');
        return response()->json($vehiculo);
    }

    /**
     * Elimina un vehículo específico (si pertenece al cliente).
     */
    public function destroy(Request $request, Vehiculo $vehiculo)
    {
        // Política de Seguridad
        if ($vehiculo->cliente_id !== $request->user()->cliente?->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        // Opcional: ¿Qué pasa si el vehículo tiene reservas futuras?
        // (Por ahora, permitimos borrarlo. En un sistema más complejo, lo impediríamos)

        $vehiculo->delete();

        return response()->json(null, 204); // 204 Sin Contenido
    }
}
