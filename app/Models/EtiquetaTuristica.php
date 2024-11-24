<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EtiquetaTuristica extends Model
{
    use HasFactory;

    // Especificar la tabla si el nombre de la tabla no sigue las convenciones de Laravel.
    protected $table = 'etiquetas_turisticas';

    // Definir los campos que pueden ser asignados masivamente.
    protected $fillable = [
        'nombre',
        'descripcion',
        'estado',
        'creado_por',
        'editado_por',
        'fecha_creacion',
        'fecha_ultima_edicion',
    ];

    // Deshabilitar la asignación automática de timestamps, ya que los nombres no siguen las convenciones estándar.
    public $timestamps = false;

    // Especificar los nombres de las columnas para las marcas de tiempo personalizadas.
    const CREATED_AT = 'fecha_creacion';
    const UPDATED_AT = 'fecha_ultima_edicion';
    
    // Relación con los locales turísticos
    public function locales()
    {
        return $this->belongsToMany(LocalTuristico::class, 'local_etiqueta', 'id_etiqueta', 'id_local')
                    ->withPivot('estado');
    }
    

}
