<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sucursal extends Model
{
    use HasFactory;

    protected $table = 'sucursals';

    protected $fillable = [
        'nombre',
        'direccion',
        'telefono',
        'esta_activa',
    ];

    protected $casts = [
        'esta_activa' => 'boolean',
    ];

    // --- RELACIONES ---

    /**
     * Una Sucursal tiene MUCHOS Horarios de Trabajo (plantillas).
     * (Relación Uno a Muchos)
     */
    public function horariosTrabajo()
    {
        return $this->hasMany(HorarioTrabajo::class, 'sucursal_id');
    }

    /**
     * Una Sucursal tiene MUCHOS Cupos Horarios (los cupos reales).
     * (Relación Uno a Muchos)
     */
    public function cuposHorarios()
    {
        return $this->hasMany(CupoHorario::class, 'sucursal_id');
    }

    /**
     * Una sucursal puede tener MUCHAS Reservas a través de sus Cupos.
     * (Relación "Has Many Through")
     */
    public function reservas()
    {
        return $this->hasManyThrough(
            Reserva::class,
            CupoHorario::class,
            'sucursal_id', // Llave foránea en cupos_horarios
            'cupo_horario_id', // Llave foránea en reservas
            'id', // Llave local en sucursales
            'id'  // Llave local en cupos_horarios
        );
    }
}
