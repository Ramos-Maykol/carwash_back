<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Servicio;
use App\Models\PrecioServicio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ServicioController extends Controller
{
    /**
     * Muestra una lista de todos los servicios con sus precios.
     */
    public function index()
    {
        // Cargar todos los servicios y sus relaciones de precios (con el tipo de vehículo)
        $servicios = Servicio::with('preciosServicio.tipoVehiculo')->get();
        return response()->json($servicios);
    }

    /**
     * Almacena un nuevo servicio y sus precios asociados.
     */
    public function store(Request $request)
    {
        $datosValidados = $request->validate([
            'nombre' => 'required|string|max:255|unique:servicios,nombre',
            'descripcion' => 'nullable|string',
            'duracion_estimada_minutos' => 'nullable|integer|min:5',
            'duracion_estimada_min' => 'nullable|integer|min:5',
            // Validar que 'precios' sea un array y que al menos tenga un elemento
            'precios' => 'required|array|min:1',
            // Validar cada elemento dentro del array 'precios'
            'precios.*.tipo_vehiculo_id' => 'required|integer|exists:tipo_vehiculos,id',
            'precios.*.precio' => 'required|numeric|min:0',
        ]);

        $duracion = $datosValidados['duracion_estimada_minutos']
            ?? $datosValidados['duracion_estimada_min']
            ?? null;

        if ($duracion === null) {
            return response()->json([
                'message' => 'El campo duracion_estimada_minutos es obligatorio.'
            ], 422);
        }

        try {
            DB::beginTransaction();

            // 1. Crear el Servicio
            $servicio = Servicio::create([
                'nombre' => $datosValidados['nombre'],
                'descripcion' => $datosValidados['descripcion'],
                'duracion_estimada_minutos' => $duracion,
            ]);

            // 2. Crear los Precios asociados
            foreach ($datosValidados['precios'] as $precio) {
                PrecioServicio::create([
                    'servicio_id' => $servicio->id,
                    'tipo_vehiculo_id' => $precio['tipo_vehiculo_id'],
                    'precio' => $precio['precio'],
                ]);
            }

            DB::commit();

            $servicio->load('preciosServicio.tipoVehiculo'); // Cargar relaciones para la respuesta
            return response()->json($servicio, 201); // 201 Creado

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al crear el servicio: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Muestra un servicio específico con sus precios.
     */
    public function show(Servicio $servicio)
    {
        $servicio->load('preciosServicio.tipoVehiculo');
        return response()->json($servicio);
    }

    /**
     * Actualiza un servicio y sus precios asociados.
     */
    public function update(Request $request, Servicio $servicio)
    {
        $datosValidados = $request->validate([
            'nombre' => ['required', 'string', 'max:255', Rule::unique('servicios', 'nombre')->ignore($servicio->id)],
            'descripcion' => 'nullable|string',
            'duracion_estimada_minutos' => 'nullable|integer|min:5',
            'duracion_estimada_min' => 'nullable|integer|min:5',
            'precios' => 'required|array|min:1',
            'precios.*.tipo_vehiculo_id' => 'required|integer|exists:tipo_vehiculos,id',
            'precios.*.precio' => 'required|numeric|min:0',
        ]);

        $duracion = $datosValidados['duracion_estimada_minutos']
            ?? $datosValidados['duracion_estimada_min']
            ?? null;

        if ($duracion === null) {
            return response()->json([
                'message' => 'El campo duracion_estimada_minutos es obligatorio.'
            ], 422);
        }

        try {
            DB::beginTransaction();

            // 1. Actualizar el Servicio
            $servicio->update([
                'nombre' => $datosValidados['nombre'],
                'descripcion' => $datosValidados['descripcion'],
                'duracion_estimada_minutos' => $duracion,
            ]);

            // 2. Sincronizar los Precios (Forma simple: borrar y volver a crear)
            // (Una forma más avanzada usaría "upsert")
            $servicio->preciosServicio()->delete(); // Borrar precios antiguos

            foreach ($datosValidados['precios'] as $precio) {
                PrecioServicio::create([
                    'servicio_id' => $servicio->id,
                    'tipo_vehiculo_id' => $precio['tipo_vehiculo_id'],
                    'precio' => $precio['precio'],
                ]);
            }

            DB::commit();

            $servicio->load('preciosServicio.tipoVehiculo');
            return response()->json($servicio);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error al actualizar el servicio: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Elimina un servicio.
     */
    public function destroy(Servicio $servicio)
    {
        // NOTA: Gracias a 'onDelete('cascade')' en la migración de 'precios_servicios',
        // al borrar el servicio, sus precios se borrarán automáticamente.
        // ADVERTENCIA: Si un servicio ya tiene reservas, esto podría fallar
        // si no configuramos la llave foránea de reservas como 'onDelete('set null')'.
        
        try {
            $servicio->delete();
            return response()->json(null, 204); // 204 Sin Contenido
        } catch (\Exception $e) {
            // Esto fallará si una reserva (tabla hija) depende de este servicio
            return response()->json([
                'message' => 'No se puede eliminar el servicio, probablemente porque ya tiene reservas asociadas.'
            ], 409); // 409 Conflicto
        }
    }
}
