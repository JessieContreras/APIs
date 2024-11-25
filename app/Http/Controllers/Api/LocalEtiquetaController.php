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
        $localEtiqueta = LocalEtiqueta::all();
        return response()->json([
            'cantidad' => $localEtiqueta->count(),
            'datos' => $localEtiqueta
        ], 200);
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
        $activos = LocalEtiqueta::where('estado', 'activo')->get();
        return response()->json([
            'cantidad' => $activos->count(),
            'datos' => $activos
        ], 200);
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

    // Actualizar una localEtiqueta existente
    public function update(Request $request, $id_local, $id_etiqueta)
    {
        // Buscar la relación localEtiqueta usando las claves compuestas
        $localEtiqueta = LocalEtiqueta::where('id_local', $id_local)
                                        ->where('id_etiqueta', $id_etiqueta)
                                        ->first();

        // Si no se encuentra la relación, devolver error
        if (!$localEtiqueta) {
            return response()->json(['message' => 'Local Etiqueta no encontrada'], 404);
        }

        // Verificar si la relación está inactiva
        if ($localEtiqueta->estado === 'inactivo') {
            return response()->json([
                'message' => 'Local Etiqueta está inactiva y no se puede actualizar.'
            ], 409);
        }

        // Validar los datos recibidos en la solicitud
        $validatedData = $request->validate([
            'id_etiqueta' => 'nullable|string', // Permitir cambiar el id_etiqueta si es necesario
            'editado_por' => 'required|string|max:255',
        ]);

        // Si se ha enviado un nuevo id_etiqueta, actualizarlo
        if ($request->has('id_etiqueta')) {
            $localEtiqueta->id_etiqueta = $validatedData['id_etiqueta'];
        }

        // Actualizar el resto de campos
        $localEtiqueta->editado_por = $validatedData['editado_por'];

        // Guardar los cambios
        $localEtiqueta->save();

        // Retornar la respuesta exitosa
        return response()->json($localEtiqueta, 200);
    }




    // Eliminar una localEtiqueta 
    public function destroy($id_local, $id_etiqueta)
    {
        // Buscar la relación localEtiqueta usando las claves compuestas
        $localEtiqueta = LocalEtiqueta::where('id_local', $id_local)
                                    ->where('id_etiqueta', $id_etiqueta)
                                    ->first();

        // Verificar si la relación existe
        if (!$localEtiqueta) {
            return response()->json(['message' => 'Local Etiqueta no encontrada'], 404);
        }

        // Verificar si ya está inactiva la etiqueta
        if ($localEtiqueta->estado === 'inactivo') {
            return response()->json(['message' => 'Este Local Etiqueta ya ha sido eliminada'], 409); // 409 Conflict
        }

        // Cambiar el estado a inactivo
        $localEtiqueta->estado = 'inactivo';
        $localEtiqueta->save();

        return response()->json(['message' => 'Local Etiqueta eliminada con éxito'], 200);
    }

}
