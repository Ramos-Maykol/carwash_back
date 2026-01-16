<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empleado extends Model
{
    use HasFactory;

    protected $table = 'empleados';

    protected $fillable = [
        'usuario_id',
        'cargo_id',
        'sucursal_id',
        'nombre',
        'apellido',
        'codigo_empleado',
        'fecha_contratacion',
        'activo',
    ];

    // --- RELACIONES ---

    /**
     * Un Empleado PERTENECE A UN Usuario.
     * (Relación Inversa de hasOne)
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    /**
     * Un Empleado PERTENECE A UN Cargo.
     * (Relación Inversa de hasMany)
     */
    public function cargo()
    {
        return $this->belongsTo(Cargo::class, 'cargo_id');
    }

    /**
     * Un Empleado PERTENECE A UNA Sucursal.
     */
    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    /**
     * Un Empleado puede tener MUCHAS Asignaciones de Reservas.
     * (Relación Uno a Muchos)
     */
    public function asignaciones()
    {
        return $this->hasMany(AsignacionReserva::class, 'empleado_id');
    }

    /**
     * RELACIÓN AVANZADA:
     * Un Empleado tiene MUCHAS Reservas A TRAVÉS DE sus Asignaciones.
     * (Relación "Has Many Through")
     */
    public function reservasAsignadas()
    {
        return $this->hasManyThrough(
            Reserva::class,             // Modelo final al que queremos llegar
            AsignacionReserva::class,   // Modelo intermedio
            'empleado_id',            // Llave foránea en la tabla intermedia (asignaciones)
            'id',                     // Llave local en la tabla final (reservas)
            'id',                     // Llave local en esta tabla (empleados)
            'reserva_id'              // Llave foránea en la tabla intermedia (asignaciones)
        );
    }
}
