<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cargo extends Model
{
    use HasFactory;

    protected $table = 'cargos';

    protected $fillable = [
        'nombre_cargo',
        'descripcion',
    ];

    // Un cargo no tiene "timestamps" (created_at, updated_at)
    // Si no los pusiste en tu migración, pon esto:
    // public $timestamps = false;

    // --- RELACIONES ---

    /**
     * Un Cargo puede tener MUCHOS Empleados.
     * (Relación Uno a Muchos)
     */
    public function empleados()
    {
        return $this->hasMany(Empleado::class, 'cargo_id');
    }
}
