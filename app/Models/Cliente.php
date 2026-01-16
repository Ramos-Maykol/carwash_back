<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;
    
    // Define la tabla si el nombre es diferente al plural
    protected $table = 'clientes';

    // Campos que se pueden llenar masivamente
    protected $fillable = [
        'usuario_id',
        'nombre',
        'apellido',
        'telefono',
        'documento_identidad',
    ];

    // --- RELACIONES ---

    /**
     * Un Cliente PERTENECE A UN Usuario.
     * (Relación Inversa de hasOne)
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    /**
     * Un Cliente puede tener MUCHOS Vehículos.
     * (Relación Uno a Muchos)
     */
    public function vehiculos()
    {
        return $this->hasMany(Vehiculo::class, 'cliente_id');
    }

    /**
     * Un Cliente puede tener MUCHAS Reservas.
     * (Relación Uno a Muchos)
     */
    public function reservas()
    {
        return $this->hasMany(Reserva::class, 'cliente_id');
    }

    public function reseñas()
    {
        // CORREGIDO: de Reseña::class a Resena::class
        return $this->hasMany(Resena::class, 'cliente_id');
    }
}
