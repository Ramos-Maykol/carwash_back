<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Empleado;
use App\Models\Reserva;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function stats(Request $request)
    {
        $today = Carbon::today();

        $todayReservasQuery = Reserva::query()
            ->whereIn('estado', ['completada', 'confirmada'])
            ->whereHas('cupoHorario', function ($q) use ($today) {
                $q->whereDate('hora_inicio', $today);
            });

        $todayWashCount = (int) $todayReservasQuery->count();

        $todayIncome = (float) $todayReservasQuery->sum('precio_final');

        $activeEmployees = (int) Empleado::query()
            ->where('activo', true)
            ->count();

        return response()->json([
            'today_wash_count' => $todayWashCount,
            'today_income' => $todayIncome,
            'active_employees' => $activeEmployees,
        ]);
    }
}
