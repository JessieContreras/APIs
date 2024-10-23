<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HorarioAtencion extends Model
{
    use HasFactory;
    protected $table = 'horarios_atencion';

    protected $fillable = [
        'id_local',
        'dia_semana',
        'hora_inicio',
        'hora_fin',
        'estado',
        'creado_por',
        'editado_por',
        'fecha_creacion',
        'fecha_ultima_edicion',
    ];

    public $timestamps = false; // Usamos las columnas `fecha_creacion` y `fecha_ultima_edicion`
}