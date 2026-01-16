<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrecioServicio extends Model
{
    use HasFactory;

    protected $table = 'precio_servicios';

    protected $fillable = [
        'servicio_id',
        'tipo_vehiculo_id',
        'precio',
    ];

    protected $casts = [
        'precio' => 'decimal:2', // Asegura que el precio se maneje como decimal
    ];

    // --- RELACIONES ---

    /**
     * Un Precio PERTENECE A UN Servicio.
     * (Relación Inversa de hasMany)
     */
    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'servicio_id');
    }

    /**
     * Un Precio PERTENECE A UN Tipo de Vehículo.
     * (Relación Inversa de hasMany)
     */
    public function tipoVehiculo()
    {
        return $this->belongsTo(TipoVehiculo::class, 'tipo_vehiculo_id');
    }

    /**
     * Un Precio (ej. "Lavado de SUV") puede estar en MUCHAS Reservas.
     * (Relación Uno a Muchos)
     */
    public function reservas()
    {
        return $this->hasMany(Reserva::class, 'precios_servicios_id');
    }
}
