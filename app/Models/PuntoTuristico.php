<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PuntoTuristico extends Model
{
    use HasFactory;
    protected $table = 'puntos_turisticos';

    protected $fillable = [
        'nombre',
        'descripcion',
        'latitud',
        'longitud',
        'id_parroquia',
        'estado',
        'creado_por',
        'editado_por',
        'fecha_creacion',
        'fecha_ultima_edicion',
    ];

    public $timestamps = false; // Usamos las columnas `fecha_creacion` y `fecha_ultima_edicion`

    public function parroquia()
    {
        return $this->belongsTo(Parroquia::class, 'id_parroquia');
    }
}