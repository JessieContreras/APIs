<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PuntoTuristicoEtiqueta extends Model
{
    use HasFactory;
    protected $table = 'puntos_turisticos_etiqueta';

    protected $fillable = [
        'id_punto_turistico',
        'id_etiqueta',
        'estado',
        'creado_por',
        'editado_por',
        'fecha_creacion',
        'fecha_ultima_edicion',
    ];

    public $timestamps = false; // Se utilizan `fecha_creacion` y `fecha_ultima_edicion` en lugar de `timestamps`
}
