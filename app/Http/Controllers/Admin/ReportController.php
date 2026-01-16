<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Reserva;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReportController extends Controller
{
    /**
     * Endpoint ÚNICO para alimentar Gráficos y Reportes.
     * Retorna JSON puro.
     */
    public function getReportData(Request $request)
    {
        try {
            // 1. Construir la consulta base con filtros
            $query = Reserva::query();

            // Filtros de Rango de Fecha
            if ($request->filled('desde') && $request->filled('hasta')) {
                $query->whereBetween('created_at', [
                    $request->string('desde')->toString() . ' 00:00:00', 
                    $request->string('hasta')->toString() . ' 23:59:59'
                ]);
            }

            // Filtro de Estado
            if ($request->filled('estado')) {
                $query->where('estado', $request->string('estado')->toString());
            }

            // 2. Obtener Datos para el LISTADO (Tablas de Word/Excel/PDF)
            // Clonamos el query para no afectar los cálculos de gráficas
            $listadoQuery = clone $query;
            $listado = $listadoQuery->with([
                    'cliente:id,nombre,apellido',
                    'precioServicio.servicio:id,nombre',
                ])
                ->orderByDesc('created_at')
                ->get()
                ->map(function ($r) {
                    return [
                        'id' => $r->id,
                        'fecha' => $r->created_at->format('Y-m-d H:i'),
                        'fecha_corta' => $r->created_at->format('d/m'), // Para eje X de gráficos si se necesita
                        'cliente' => trim(($r->cliente->nombre ?? '') . ' ' . ($r->cliente->apellido ?? '')),
                        'servicio' => $r->precioServicio->servicio->nombre ?? 'N/A',
                        'monto' => (float) ($r->precio_final ?? 0),
                        'estado' => $r->estado,
                    ];
                });

            // 3. Obtener Datos para GRÁFICOS (KPIs y Agrupaciones)
            // Esto evita que tengas que sumar en JavaScript
            
            // Gráfico 1: Total Ingresos vs Cantidad Lavados
            $totalIngresos = (clone $query)->sum('precio_final');
            $totalLavados = (clone $query)->count();

            // Gráfico 2: Lavados por Estado (Pie Chart)
            $porEstado = (clone $query)
                ->select('estado', DB::raw('count(*) as total'))
                ->groupBy('estado')
                ->pluck('total', 'estado'); // Devuelve formato: ['pendiente' => 5, 'completado' => 10]

            // Gráfico 3: Ingresos por Día (Bar/Line Chart) - Opcional para gráfico de tendencias
            $porDia = (clone $query)
                 ->select(DB::raw('DATE(created_at) as dia'), DB::raw('SUM(precio_final) as total'))
                 ->groupBy('dia')
                 ->orderBy('dia')
                 ->get();

            // 4. Respuesta JSON Estructurada
            return response()->json([
                'meta' => [
                    'fecha_generacion' => now()->toDateTimeString(),
                    'filtros_aplicados' => $request->all()
                ],
                'kpis' => [
                    'total_ingresos' => $totalIngresos,
                    'total_lavados' => $totalLavados,
                    'distribucion_estado' => $porEstado,
                    'tendencia_ingresos' => $porDia
                ],
                'data' => $listado // Array limpio para las tablas
            ]);

        } catch (\Throwable $e) {
            Log::error('Error generando datos de reporte: ' . $e->getMessage());
            return response()->json(['error' => 'Error al procesar datos del reporte'], 500);
        }
    }
}