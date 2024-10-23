<?php

namespace App\Http\Controllers\Api;

use App\Models\LocalEtiqueta;
use App\Models\LocalTuristico;
use App\Models\EtiquetaTuristica;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LocalEtiquetaController extends Controller
{
    // Listar toda la localEtiqueta 
    public function index()
    {
        return LocalEtiqueta::all();
    }

    // Mostrar una localEtiqueta  específica
    public function show( string $id)
    {
        $localEtiqueta = LocalEtiqueta::where('id_local', $id)->first();
        $nombreEtiqueta = EtiquetaTuristica::Where('id', $localEtiqueta->id_etiqueta)->first();
        $nombreLocal = LocalTuristico::Where('id', $localEtiqueta->id_local)->first();
        if (!$localEtiqueta) {
            return response()->json(['message' => 'Local Etiqueta no encontrada'], 404);
        }
        

        // Retornar una respuesta exitosa
        return response()->json([
            $localEtiqueta,
            'Etiqueta' => $nombreEtiqueta->nombre,
            'Local' => $nombreLocal->nombre
        ], 200);
    }

    // Mostrar todos los LocalEtiqueta activos
    public function indexActivos()
    {
        return response()->json(LocalEtiqueta::where('estado', 'activo')->get(), 200);
    }

    // Buscar una localEtiqueta por ID solo si está activo
    public function showActivo(string $id)
    {
        $localEtiqueta = LocalEtiqueta::where('id_local', $id)->where('estado', 'activo')->first();
        $nombreEtiqueta = EtiquetaTuristica::Where('id', $localEtiqueta->id_etiqueta)->first();
        $nombreLocal = LocalTuristico::Where('id', $localEtiqueta->id_local)->first();
    
        if (!$localEtiqueta) {
            return response()->json(['message' => 'Local Etiqueta no encontrada o no está activa'], 404);
        }
        $localEtiqueta['nombreEtiqueta'] = $nombreEtiqueta->nombre;
    
        // Retornar una respuesta exitosa
        return response()->json([
            $localEtiqueta,
            'Etiqueta' => $nombreEtiqueta->nombre,
            'Local' => $nombreLocal->nombre
        ], 200);
    }
    
    

    // Crear una nueva localEtiqueta 
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'id_local' => 'required|string',
            'id_etiqueta' => 'required|string',
            'creado_por' => 'required|string|max:255',
        ]);

        // Agregar el estado 'activo' por defecto al array de datos
        $validatedData['estado'] = 'activo';
        $localEtiqueta = LocalEtiqueta::create($validatedData);

        return response()->json($localEtiqueta, 201);
    }

    // Actualizar una localEtiqueta  existente
    public function update(Request $request, $id)
    {
        $localEtiqueta = LocalEtiqueta::find($id);

        if (!$localEtiqueta) {
            return response()->json(['message' => 'Local Etiqueta no encontrada'], 404);
        }

        // Verificar si la etiqueta ya fue eliminado (estadp = 'inactivo')
        if ($localEtiqueta->estado === 'inactivo') {
            return response()->json([
                'message' => 'Local Etiqueta  está inactiva y no se puede actualizar.'
            ], 409);
        }

        $validatedData = $request->validate([
            'id_local' => 'required|string',
            'id_etiqueta' => 'required|string',
            'editado_por' => 'required|string|max:255',
        ]);

        $localEtiqueta->update($validatedData);

        return response()->json($localEtiqueta, 200);
    }

    // Eliminar una localEtiqueta 
    public function destroy($id)
    {
        $localEtiqueta = LocalEtiqueta::find($id);

        if (!$localEtiqueta) {
            return response()->json(['message' => 'Local Etiqueta no encontrada'], 404);
        }

        // Verificar si ya está inactiva la etiqueta
        if ($localEtiqueta->estado === 'inactivo') {
            return response()->json(['message' => 'Este Local Etiqueta ya ha sido eliminada'], 409); // 409 Conflict
        }

        // Cambiar el estado a eliminado
        $localEtiqueta->estado = 'inactivo'; 
        $localEtiqueta->save(); 

        return response()->json(['message' => 'Local Etiqueta eliminada con éxito'], 200);

    }
}
