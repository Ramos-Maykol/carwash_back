<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    use HasFactory;

    protected $table = 'pagos';

    protected $fillable = [
        'reserva_id',
        'monto',
        'metodo_pago', // 'efectivo', 'tarjeta_app', 'tarjeta_pos'
        'estado',      // 'pendiente', 'completado', 'fallido'
        'id_transaccion_gateway',
    ];

    /**
     * Casts para manejar tipos de datos.
     */
    protected $casts = [
        'monto' => 'decimal:2',
    ];

    // --- RELACIONES ---

    /**
     * Un Pago PERTENECE A UNA Reserva.
     * (Relación Inversa de hasOne)
     */
    public function reserva()
    {
        return $this->belongsTo(Reserva::class, 'reserva_id');
    }

    /**
     * Un pago es realizado por un cliente (a través de la reserva).
     * Esta es una relación "Has One Through".
     */
    public function cliente()
    {
        return $this->hasOneThrough(
            Cliente::class,
            Reserva::class,
            'id',             // Llave local en Reservas
            'id',             // Llave local en Clientes
            'reserva_id',     // Llave foránea en Pagos (que conecta con Reserva)
            'cliente_id'    // Llave foránea en Reservas (que conecta con Cliente)
        );
    }
}
