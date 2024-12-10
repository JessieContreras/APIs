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

    public $timestamps = false; // No usa created_at ni updated_at

    public $incrementing = false; // No es una clave primaria autoincremental
    protected $primaryKey = null; // Indicar que no hay clave primaria
}
