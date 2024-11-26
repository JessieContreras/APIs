<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DuenoLocal extends Model
{
    use HasFactory;

    protected $table = 'duenos_locales';

    protected $fillable = [
        'nombre',
        'apellido',
        'cedula',
        'telefono',
        'email',
        'contrasena',
        'estado',
        'creado_por',
        'editado_por',
        'fecha_creacion',
        'fecha_ultima_edicion',
    ];

    public $timestamps = false;

    const CREATED_AT = 'fecha_creacion';
    const UPDATED_AT = 'fecha_ultima_edicion';

    // DuenoLocal.php
    public function locales()
    {
        return $this->hasMany(LocalTuristico::class, 'id_dueno');
    }


}
