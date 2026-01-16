<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsignacionReserva extends Model
{
    protected $table = 'asignaciones_reservas';

    protected $fillable = [
        'reserva_id',
        'empleado_id',
        // 'horas_trabajadas', // Ejemplo de dato extra
    ];

    // --- RELACIONES ---

    /**
     * Una Asignación PERTENECE A UNA Reserva.
     */
    public function reserva()
    {
        return $this->belongsTo(Reserva::class, 'reserva_id');
    }

    /**
     * Una Asignación PERTENECE A UN Empleado.
     */
    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }
}
