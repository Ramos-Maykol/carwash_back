<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reserva;
use App\Models\CupoHorario;
use App\Models\PrecioServicio;
use App\Models\Vehiculo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class ReservaController extends Controller
{
    /**
     * Muestra una lista de las reservas del cliente autenticado.
     * Este endpoint es PROTEGIDO (requiere autenticación).
     */
    public function index(Request $request)
    {
        // 1. Obtener el usuario autenticado y su perfil de cliente
        $usuario = $request->user();
        $cliente = $usuario->cliente;

        if (!$cliente) {
            return response()->json(['message' => 'El perfil de cliente no fue encontrado para este usuario.'], 404);
        }

        // 2. Obtener las reservas del cliente
        // Usamos 'with' (Eager Loading) para traer toda la información relacionada
        // en una sola consulta eficiente.
        $reservas = $cliente->reservas()
            ->with([
                'cupoHorario',                   // Carga los datos del cupo (fecha, hora)
                'vehiculo.tipoVehiculo',         // Carga el vehículo y su tipo (ej. "SUV")
                'precioServicio.servicio'        // Carga el servicio (ej. "Lavado Básico")
            ])
            ->orderBy('id', 'desc') // Mostrar las más recientes primero
            ->get();

        // 3. Devolver las reservas como JSON
        return response()->json($reservas);
    }

    /**
     * Almacena una nueva reserva en la base de datos.
     * Este endpoint es PROTEGIDO (requiere autenticación).
     */
    public function store(Request $request)
    {
        // 1. Obtener el usuario autenticado y su perfil de cliente
        $usuario = $request->user();
        $cliente = $usuario->cliente; // Usamos la relación que definimos

        if (!$cliente) {
            return response()->json(['message' => 'El perfil de cliente no fue encontrado para este usuario.'], 404);
        }

        // 2. Validar los datos de entrada
        $datosValidados = $request->validate([
            'cupo_horario_id' => [
                'required',
                'integer',
                // Validar que el cupo exista Y que esté 'disponible'
                Rule::exists('cupo_horarios', 'id')->where('estado', 'disponible')
            ],
            'vehiculo_id' => [
                'required',
                'integer',
                // Validar que el vehículo exista Y que pertenezca al cliente autenticado
                Rule::exists('vehiculos', 'id')->where('cliente_id', $cliente->id)
            ],
            'precio_servicio_id' => 'required|integer|exists:precio_servicios,id',
        ]);

        // 3. Obtener los modelos que necesitamos
        $cupo = CupoHorario::findOrFail($datosValidados['cupo_horario_id']);
        $precioServicio = PrecioServicio::findOrFail($datosValidados['precio_servicio_id']);

        // 4. Doble chequeo de seguridad (opcional pero recomendado)
        // Asegurarse de que el cupo no sea en el pasado
        $inicioCupo = Carbon::parse($cupo->hora_inicio);
        if ($inicioCupo->lessThanOrEqualTo(Carbon::now())) {
            return response()->json(['message' => 'No puedes reservar un cupo que ya ha pasado.'], 422);
        }

        // 5. La Transacción de Base de Datos (¡La parte más importante!)
        try {
            DB::beginTransaction(); // <-- Iniciar transacción

            // Paso A: Actualizar el cupo
            $cupo->estado = 'reservado';
            $cupo->save();

            // Paso B: Crear la reserva
            $reserva = Reserva::create([
                'cliente_id' => $cliente->id,
                'cupo_horario_id' => $cupo->id,
                'vehiculo_id' => $datosValidados['vehiculo_id'],
                'precios_servicios_id' => $precioServicio->id, // ✅ Campo correcto
                'precio_final' => $precioServicio->precio, // Guardamos el precio en el momento
                'estado' => 'confirmada', // O 'pendiente' si requiere aprobación
            ]);
            
            // (Opcional: Crear un registro de pago 'pendiente')
            // $reserva->pagos()->create([
            //     'monto' => $precioServicio->precio,
            //     'estado' => 'pendiente',
            //     'metodo_pago' => 'por_definir'
            // ]);

            DB::commit(); // <-- Confirmar transacción si todo salió bien

            // 6. Devolver la reserva creada
            // Usamos 'load' para incluir la información del cupo y vehículo en la respuesta
            $reserva->load('cupoHorario', 'vehiculo');

            return response()->json([
                'message' => '¡Reserva creada exitosamente!',
                'reserva' => $reserva
            ], 201); // 201 Creado

        } catch (\Exception $e) {
            DB::rollBack(); // <-- Revertir todo si algo falló
            
            // \Illuminate\Support\Facades\Log::error('Error al crear reserva: ' . $e->getMessage()); // Para depuración
            
            return response()->json([
                'message' => 'Error al procesar la reserva. Por favor, inténtelo de nuevo.',
                'error' => $e->getMessage() // Ocultar en producción
            ], 500); // 500 Error de Servidor
        }
    }

    /**
     * Cambia el estado de una reserva.
     * - Cliente: solo puede cancelar su propia reserva y solo si el cupo es futuro.
     * - Admin/Empleado: puede pasar a 'en_proceso' o 'completada'.
     */
    public function updateEstado(Request $request, Reserva $reserva)
    {
        $datosValidados = $request->validate([
            'estado' => ['required', 'string'],
        ]);

        $estadoSolicitado = strtolower(trim($datosValidados['estado']));

        // Normalizar aliases
        $estadoMap = [
            'confirmado' => 'confirmada',
            'terminado' => 'completada',
            'cancelado' => 'cancelada',
        ];
        $estadoNuevo = $estadoMap[$estadoSolicitado] ?? $estadoSolicitado;

        $estadosPermitidos = ['pendiente', 'confirmada', 'en_proceso', 'completada', 'cancelada'];
        if (!in_array($estadoNuevo, $estadosPermitidos, true)) {
            return response()->json([
                'message' => 'Estado inválido.'
            ], 422);
        }

        $user = $request->user();
        $esAdminOEmpleado = $user && ($user->hasRole('admin') || $user->hasRole('empleado'));

        // Cargar cupo para validaciones de tiempo
        $reserva->loadMissing('cupoHorario');

        // Cliente
        if (!$esAdminOEmpleado) {
            $cliente = $user?->cliente;
            if (!$cliente || $reserva->cliente_id !== $cliente->id) {
                return response()->json(['message' => 'No autorizado.'], 403);
            }

            if ($estadoNuevo !== 'cancelada') {
                return response()->json([
                    'message' => 'No autorizado. Un cliente solo puede cancelar su reserva.'
                ], 403);
            }

            // Solo permitir cancelación si el cupo es futuro
            if (!$reserva->cupoHorario) {
                return response()->json(['message' => 'Cupo horario no encontrado para la reserva.'], 409);
            }

            $inicioCupo = Carbon::parse($reserva->cupoHorario->hora_inicio);
            if ($inicioCupo->lessThanOrEqualTo(Carbon::now())) {
                return response()->json([
                    'message' => 'No puedes cancelar una reserva con fecha/hora pasada.'
                ], 422);
            }

            // Permitir cancelar solo si aún no está finalizada
            if (in_array($reserva->estado, ['completada', 'cancelada'], true)) {
                return response()->json([
                    'message' => 'La reserva ya no puede ser modificada.'
                ], 409);
            }

            DB::beginTransaction();
            try {
                $reserva->estado = 'cancelada';
                $reserva->save();

                // Liberar cupo
                $reserva->cupoHorario->estado = 'disponible';
                $reserva->cupoHorario->save();

                DB::commit();

                return response()->json([
                    'message' => 'Reserva cancelada exitosamente.',
                    'reserva' => $reserva->fresh()->load('cupoHorario', 'vehiculo', 'precioServicio.servicio')
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Error al cancelar la reserva.',
                ], 500);
            }
        }

        // Admin/Empleado
        if (!in_array($estadoNuevo, ['en_proceso', 'completada'], true)) {
            return response()->json([
                'message' => 'Transición no permitida para tu rol.'
            ], 403);
        }

        if (in_array($reserva->estado, ['cancelada', 'completada'], true)) {
            return response()->json([
                'message' => 'La reserva ya no puede ser modificada.'
            ], 409);
        }

        $reserva->estado = $estadoNuevo;
        $reserva->save();

        return response()->json([
            'message' => 'Estado de la reserva actualizado.',
            'reserva' => $reserva->fresh()->load('cupoHorario', 'vehiculo', 'precioServicio.servicio')
        ]);
    }
}

