<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehiculo extends Model
{
    use HasFactory;

    protected $table = 'vehiculos';

    protected $fillable = [
        'cliente_id',
        'tipo_vehiculo_id',
        'marca',
        'modelo',
        'placa', // 'patente' en otros países
        'color',
    ];

    // --- RELACIONES ---

    /**
     * Un Vehículo PERTENECE A UN Cliente.
     * (Relación Inversa de hasMany)
     */
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    /**
     * Un Vehículo PERTENECE A UN Tipo de Vehículo (ej. "SUV").
     * (Relación Inversa de hasMany)
     */
    public function tipoVehiculo()
    {
        return $this->belongsTo(TipoVehiculo::class, 'tipo_vehiculo_id');
    }

    /**
     * Un Vehículo puede tener MUCHAS Reservas (su historial de lavados).
     * (Relación Uno a Muchos)
     */
    public function reservas()
    {
        return $this->hasMany(Reserva::class, 'vehiculo_id');
    }
}
