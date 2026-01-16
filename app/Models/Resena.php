<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Resena extends Model
{
    use HasFactory;

    protected $table = 'reseñas';

    protected $fillable = [
        'reserva_id',
        'cliente_id',
        'calificacion', // 1-5
        'comentario',
    ];

    /**
     * Casts para manejar tipos de datos.
     */
    protected $casts = [
        'calificacion' => 'integer',
    ];

    // --- RELACIONES ---

    /**
     * Una Reseña PERTENECE A UNA Reserva.
     * (Relación Inversa de hasOne)
     */
    public function reserva()
    {
        return $this->belongsTo(Reserva::class, 'reserva_id');
    }

    /**
     * Una Reseña PERTENECE A UN Cliente.
     * (Relación Inversa de hasMany)
     */
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }
}
