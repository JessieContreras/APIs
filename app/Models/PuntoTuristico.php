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
    ];

    public $timestamps = false;

    // Relación con la parroquia
    public function parroquia()
    {
        return $this->belongsTo(Parroquia::class, 'id_parroquia');
    }
    // Relación con Etiquetas
    public function etiquetas()
    {
        return $this->belongsToMany(EtiquetaTuristica::class, 'puntos_turisticos_etiqueta', 'id_punto_turistico', 'id_etiqueta')
                    ->withPivot('estado');
    }



}
