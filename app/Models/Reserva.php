<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reserva extends Model
{
    use HasFactory;

    protected $table = 'reservas';

    protected $fillable = [
        'cliente_id',
        'cupo_horario_id',  // ¡Este es único!
        'vehiculo_id',
        'precios_servicios_id', // El precio específico (ej. "Lavado SUV")
        'precio_final',         // El precio al momento de la reserva
        'estado',               // 'pendiente', 'confirmada', 'en_proceso', 'completada', 'cancelada'
        'notas_cliente',
    ];

    /**
     * Casts para manejar tipos de datos.
     */
    protected $casts = [
        'precio_final' => 'decimal:2',
    ];

    // --- RELACIONES ---

    /**
     * Una Reserva PERTENECE A UN Cliente.
     * (Relación Inversa de hasMany)
     */
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    /**
     * Una Reserva PERTENECE A UN Cupo Horario.
     * (Relación Inversa de hasOne)
     */
    public function cupoHorario()
    {
        return $this->belongsTo(CupoHorario::class, 'cupo_horario_id');
    }

    /**
     * Una Reserva PERTENECE A UN Vehículo.
     * (Relación Inversa de hasMany)
     */
    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class, 'vehiculo_id');
    }

    /**
     * Una Reserva PERTENECE A UN Precio de Servicio específico.
     * (Relación Inversa de hasMany)
     */
    public function precioServicio()
    {
        return $this->belongsTo(PrecioServicio::class, 'precios_servicios_id');
    }

    /**
     * Una Reserva tiene UN Pago asociado.
     * (Relación Uno a Uno)
     */
    public function pago()
    {
        return $this->hasOne(Pago::class, 'reserva_id');
    }

    /**
     * Una Reserva tiene UNA Reseña asociada.
     * (Relación Uno a Uno)
     */
    public function reseña()
    {
        return $this->hasOne(Resena::class, 'reserva_id');
    }

    /**
     * Una Reserva puede ser atendida por MUCHOS Empleados.
     * (Relación Muchos a Muchos)
     */
    public function empleados()
    {
        return $this->belongsToMany(
            Empleado::class,
            'asignaciones_reservas', // Nombre de la tabla pivote
            'reserva_id',            // Llave foránea de Reserva en la pivote
            'empleado_id'          // Llave foránea de Empleado en la pivote
        )->withTimestamps(); // Opcional: si la tabla pivote tiene timestamps
    }
}
