<?php

namespace App\Http\Controllers\Api;

use App\Models\EtiquetaTuristica;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EtiquetaTuristicaController extends Controller
{
    // Listar todas las etiquetas turísticas
    public function index()
    {
        return response()->json(EtiquetaTuristica::all(), 200);
    }

    // Mostrar una etiqueta turística específica
    public function show($id)
    {
        $etiquetaTuristica = EtiquetaTuristica::find($id);

        if (!$etiquetaTuristica) {
            return response()->json(['message' => 'Etiqueta turística no encontrada'], 404);
        }

        return response()->json($etiquetaTuristica, 200);
    }

    // Mostrar todos los EtiquetaTuristica activos
    public function indexActivos()
    {
        return response()->json(EtiquetaTuristica::where('estado', 'activo')->get(), 200);
    }

    // Buscar una etiquetaTuristica por ID solo si está activo
    public function showActivo(string $id)
    {
        $etiquetaTuristica = EtiquetaTuristica::where('id', $id)->where('estado', 'activo')->first();
    
        if (!$etiquetaTuristica) {
            return response()->json(['message' => 'Etiqueta turística no encontrada o no está activa'], 404);
        }
    
        return response()->json($etiquetaTuristica, 200);
    }
    


 
    // Crear una nueva etiqueta turística
    public function store(Request $request)
    {
        // Validar los datos
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'creado_por' => 'required|string|max:255',
        ]);
        
        // Verificar si ya existe una etiqueta turística con el mismo nombre que esté activa
        $etiquetaTuristicaExistente = EtiquetaTuristica::where('nombre', $request->nombre)
            ->where('estado', 'activo') // Solo bloquear si está activa
            ->first(); 

        if ($etiquetaTuristicaExistente) {
            // Si ya existe una etiqueta con el mismo nombre y está activa
            return response()->json([
                'message' => 'La etiqueta turística ya existe y está activa.'
            ], 409);
        }

        // Agregar el estado 'activo' por defecto al array de datos
        $validatedData['estado'] = 'activo';

        // Crear la nueva etiqueta turística
        $etiquetaTuristica = EtiquetaTuristica::create($validatedData);

        // Retornar la respuesta de éxito
        return response()->json($etiquetaTuristica, 201);
    }


 
    // Actualizar una etiqueta turística existente
    public function update(Request $request, $id)
    {
        $etiquetaTuristica = EtiquetaTuristica::find($id);

        if (!$etiquetaTuristica) {
            return response()->json(['message' => 'Etiqueta turística no encontrada'], 404);
        }

        // Verificar si la etiqueta ya fue eliminado (estadp = 'inactivo')
        if ($etiquetaTuristica->estado === 'inactivo') {
            return response()->json([
                'message' => 'La etiqueta turística está inactiva y no se puede actualizar.'
            ], 409);
        }

        $validatedData = $request->validate([
            'nombre' => 'sometimes|string|max:255',
            'descripcion' => 'nullable|string',
            'editado_por' => 'required|string|max:255',
        ]);

        $etiquetaTuristica->update($validatedData);

        return response()->json($etiquetaTuristica, 200);
    }
 
    // Eliminar una etiqueta turística
    public function destroy($id)
    {
        $etiquetaTuristica = EtiquetaTuristica::find($id);

        if (!$etiquetaTuristica) {
            return response()->json(['message' => 'Etiqueta turística no encontrada'], 404);
        }

        // Verificar si ya está inactiva la etiqueta
        if ($etiquetaTuristica->estado === 'inactivo') {
            return response()->json(['message' => 'Esta etiqueta turistica ya ha sido eliminada'], 409); // 409 Conflict
        }

        // Cambiar el estado a eliminado
        $etiquetaTuristica->estado = 'inactivo'; 
        $etiquetaTuristica->save(); 

        return response()->json(['message' => 'Etiqueta Turistica eliminada con éxito'], 200);

        
    }
}
