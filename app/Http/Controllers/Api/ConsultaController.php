<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DuenoLocal;
use App\Models\Asistente;
use App\Models\EtiquetaTuristica;
use App\Models\LocalTuristico;
use App\Models\PuntoTuristico;
use App\Models\Parroquia;
use App\Models\Anuncio;



class ConsultaController extends Controller
{
    public function ContarTodo() //Para el Administrador
    {
        $asistentes = Asistente::orderBy('id', 'desc')->get();
        $duenoLocal = DuenoLocal::orderBy('id', 'desc')->get(); 
        $etiquetaTuristica = EtiquetaTuristica::orderBy('id', 'desc')->get(); 
        $localesTuristicos = LocalTuristico::orderBy('id', 'desc')->get();
        $puntosTuristicos = PuntoTuristico::orderBy('id', 'desc')->get();
        $parroquia = Parroquia::orderBy('id', 'desc')->get();
        $anuncios = Anuncio::orderBy('id', 'desc')->get();
        return response()->json([
            'asistentes' => $asistentes->count(),
            'dueñoLocal' => $duenoLocal->count(),
            'etiquetaTuristica' => $etiquetaTuristica->count(),
            'localesTuristicos' => $localesTuristicos->count(),
            'puntosTuristicos' => $puntosTuristicos->count(),
            'parroquia' => $parroquia->count(),
            'anuncios' => $anuncios->count()
        ], 200);
    }

}
