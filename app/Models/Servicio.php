<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Servicio extends Model
{
    use HasFactory;

    protected $table = 'servicios';

    protected $fillable = [
        'nombre',
        'descripcion',
        'duracion_estimada_minutos',
    ];

    // --- RELACIONES ---

    /**
     * Un Servicio (ej. "Encerado") tiene MUCHOS Precios de Servicio.
     * (Uno para Sedan, uno para SUV, uno para Camioneta)
     * (Relación Uno a Muchos)
     */
    public function preciosServicio()
    {
        return $this->hasMany(PrecioServicio::class, 'servicio_id');
    }
}
