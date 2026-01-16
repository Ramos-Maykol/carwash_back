<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HorarioTrabajo extends Model
{
    use HasFactory;

    protected $table = 'horario_trabajos';

    protected $fillable = [
        'sucursal_id',
        'empleado_id', // Puede ser null si el horario es de la sucursal
        'dia_semana',  // 1=Lunes, 2=Martes, ..., 7=Domingo
        'hora_inicio', // ej. "09:00:00"
        'hora_fin',    // ej. "17:00:00"
        'esta_activo',
    ];

    protected $casts = [
        'dia_semana' => 'integer',
        'esta_activo' => 'boolean',
    ];

    // --- RELACIONES ---

    /**
     * Un Horario de Trabajo PERTENECE A UNA Sucursal.
     * (Relación Inversa de hasMany)
     */
    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    /**
     * Un Horario de Trabajo PUEDE PERTENECER A UN Empleado.
     * (Relación Inversa de hasMany)
     */
    public function empleado()
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }
}
