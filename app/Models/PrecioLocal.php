<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrecioLocal extends Model
{
    use HasFactory;
    protected $table = 'precios_locales';

    protected $fillable = [
        'id_local',
        'servicio',
        'precio',
        'creado_por',
        'editado_por',
        'estado',
        'fecha_creacion',
        'fecha_ultima_edicion',
    ];

    public $timestamps = false; // Usamos las columnas `fecha_creacion` y `fecha_ultima_edicion`
}