<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoVehiculo extends Model
{
    use HasFactory;

    protected $table = 'tipo_vehiculos';

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    // --- RELACIONES ---

    /**
     * Un Tipo de Vehículo (ej. "SUV") puede tener MUCHOS Vehículos.
     * (Relación Uno a Muchos)
     */
    public function vehiculos()
    {
        return $this->hasMany(Vehiculo::class, 'tipo_vehiculo_id');
    }

    /**
     * Un Tipo de Vehículo puede tener MUCHOS Precios de Servicios.
     * (ej. "Precio de SUV para Lavado", "Precio de SUV para Encerado")
     * (Relación Uno a Muchos)
     */
    public function preciosServicios()
    {
        return $this->hasMany(PrecioServicio::class, 'tipo_vehiculo_id');
    }
}
