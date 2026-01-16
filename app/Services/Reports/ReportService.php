<?php

namespace App\Services\Reports;

use App\Models\Reserva;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function getMetrics()
    {
        return [
            'ingresos_por_mes' => $this->ingresosPorMes(),
            'reservas_por_estado' => $this->reservasPorEstado(),
            'top_servicios' => $this->topServicios(),
            'kpis' => $this->getMainKpis()
        ];
    }

    private function getMainKpis() {
        return [
            'total_ventas' => Reserva::where('estado', 'completada')->sum('precio_final'),
            'total_reservas' => Reserva::count(),
            'reservas_pendientes' => Reserva::where('estado', 'pendiente')->count()
        ];
    }

    private function ingresosPorMes()
    {
        // Agrupa ventas completadas por mes del año actual
        return Reserva::select(
                DB::raw('SUM(precio_final) as total'),
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as mes")
            )
            ->where('estado', 'completada')
            ->whereYear('created_at', date('Y'))
            ->groupBy('mes')
            ->orderBy('mes')
            ->get();
    }

    private function reservasPorEstado()
    {
        return Reserva::select('estado', DB::raw('count(*) as total'))
            ->groupBy('estado')
            ->get();
    }

    private function topServicios()
    {
        // Join para obtener nombres de servicios más vendidos
        return DB::table('reservas')
            ->join('precio_servicios', 'reservas.precios_servicios_id', '=', 'precio_servicios.id')
            ->join('servicios', 'precio_servicios.servicio_id', '=', 'servicios.id')
            ->select('servicios.nombre', DB::raw('count(*) as total_ventas'))
            ->where('reservas.estado', 'completada')
            ->groupBy('servicios.nombre')
            ->orderByDesc('total_ventas')
            ->limit(5)
            ->get();
    }
}