<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LocalTuristico extends Model
{
    use HasFactory;

    protected $table = 'locales_turisticos';

    protected $fillable = [
        'nombre',
        'descripcion',
        'id_dueno',
        'direccion',
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

    // Relación de muchos a muchos con EtiquetaTuristica
    public function etiquetas()
    {
        return $this->belongsToMany(EtiquetaTuristica::class, 'local_etiqueta', 'id_local', 'id_etiqueta')
                    ->withPivot('estado');
    }
}
