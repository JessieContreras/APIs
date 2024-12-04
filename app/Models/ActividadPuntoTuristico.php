<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActividadPuntoTuristico extends Model
{
    use HasFactory;

    protected $table = 'actividad_punto_turistico';

    protected $fillable = [
        'id_punto_turistico',
        'actividad',
        'precio',
        'estado',
        'creado_por',
        'editado_por',
        'fecha_creacion',
        'fecha_ultima_edicion',
        'tipo',
    ];

    public $timestamps = false; // Usamos `fecha_creacion` y `fecha_ultima_edicion` en lugar de timestamps predeterminados de Laravel
}
