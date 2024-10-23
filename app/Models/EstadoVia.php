<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstadoVia extends Model
{
    use HasFactory;

    protected $table = 'estado_vias';

    protected $fillable = [
        'nombre_via',
        'estado',
        'comentarios',
        'eliminado',
        'creado_por',
        'editado_por',
        'fecha_creacion',
        'fecha_ultima_edicion',
    ];

    public $timestamps = false;

    const CREATED_AT = 'fecha_creacion';
    const UPDATED_AT = 'fecha_ultima_edicion';

}
