<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServicioLocal extends Model
{
    use HasFactory;

    protected $table = 'servicios_locales';

    protected $fillable = [
        'id_local',
        'servicio',
        'precio',
        'tipo',
        'estado',
        'creado_por',
        'editado_por',
        'fecha_creacion',
        'fecha_ultima_edicion',
    ];

    public $timestamps = false; // Usamos `fecha_creacion` y `fecha_ultima_edicion` en lugar de timestamps predeterminados de Laravel
}
