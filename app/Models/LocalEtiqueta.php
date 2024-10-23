<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LocalEtiqueta extends Model
{
    use HasFactory;
    protected $table = 'local_etiqueta';

    protected $fillable = [
        'id_local',
        'id_etiqueta',
        'estado',
        'creado_por',
        'editado_por',
        'fecha_creacion',
        'fecha_ultima_edicion',
    ];

    public $timestamps = false; // Usamos las columnas `fecha_creacion` y `fecha_ultima_edicion`
}
