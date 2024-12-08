<?php

namespace App\Http\Controllers\Api;

use App\Models\InformacionGeneral;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class InformacionGeneralController extends Controller
{
    public function index()
    {
        $informacionGeneral = InformacionGeneral::orderBy('id', 'desc')->get(); 
        return response()->json([
            'cantidad' => $informacionGeneral->count(),
            'datos' => $informacionGeneral
        ], 200);
    }

    public function show($id)
    {
        $informacionGeneral = InformacionGeneral::find($id);

        if (!$informacionGeneral) {
            return response()->json(['message' => 'Información general no encontrada'], 404);
        }

        return response()->json($informacionGeneral, 200);
    }

    public function indexActivos()
    {
        $activos = InformacionGeneral::where('estado', 'activo')->orderBy('id', 'desc')->get();
        return response()->json([
            'cantidad' => $activos->count(),
            'datos' => $activos
        ], 200);
    }

    // Buscar una informacionGeneral por ID solo si está activo
    public function showActivo(string $id)
    {
        $informacionGeneral = InformacionGeneral::where('id', $id)->where('estado', 'activo')->first();
    
        if (!$informacionGeneral) {
            return response()->json(['message' => 'Información General no encontrada o no está activa'], 404);
        }
    
        return response()->json($informacionGeneral, 200);
    }
    
    

    // Crear una nueva información general
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'mision' => 'required|string',
            'vision' => 'required|string',
            'detalles' => 'nullable|string',
            'encargado' => 'required|string|max:255',
            'version_aplicacion' => 'required|string|max:50',
            'creado_por' => 'required|string|max:255',
        ]);

        // Agregar el estado 'activo' por defecto al array de datos
        $validatedData['estado'] = 'activo';
        $informacionGeneral = InformacionGeneral::create($validatedData);

        return response()->json($informacionGeneral, 201);
    }

    // Actualizar una información general existente
    public function update(Request $request, $id)
    {
        $informacionGeneral = InformacionGeneral::find($id);

        if (!$informacionGeneral) {
            return response()->json(['message' => 'Información general no encontrada'], 404);
        }

        // Verificar si la etiqueta ya fue eliminado (estadp = 'inactivo')
        if ($informacionGeneral->estado === 'inactivo') {
            return response()->json([
                'message' => 'La información general está inactiva y no se puede actualizar.'
            ], 409);
        }

        $validatedData = $request->validate([
            'mision' => 'sometimes|string',
            'vision' => 'sometimes|string',
            'detalles' => 'nullable|string',
            'encargado' => 'sometimes|string|max:255',
            'version_aplicacion' => 'sometimes|string|max:50',
            'editado_por' => 'required|string|max:255',
        ]);

        $informacionGeneral->update($validatedData);

        return response()->json($informacionGeneral, 200);
    }

    // Eliminar una información general
    public function destroy($id)
    {
        $informacionGeneral = InformacionGeneral::find($id);

        if (!$informacionGeneral) {
            return response()->json(['message' => 'Información general no encontrada'], 404);
        }

        // Verificar si ya está inactiva la etiqueta
        if ($informacionGeneral->estado === 'inactivo') {
            return response()->json(['message' => 'Esta informacion general ya ha sido eliminada'], 409); // 409 Conflict
        }

        // Cambiar el estado a eliminado
        $informacionGeneral->estado = 'inactivo'; 
        $informacionGeneral->save(); 

        return response()->json(['message' => 'Informacion general eliminada con éxito'], 200);

    }
    
    public function activar(string $id)
    {
        $informacionGeneral = InformacionGeneral::find($id);

        if (!$informacionGeneral) {
            return response()->json(['message' => 'InformacionGeneral no encontrado'], 404);
        }

        if ($informacionGeneral->estado !== 'inactivo') {
            return response()->json(['message' => 'El InformacionGeneral ya se encuentra activo'], 403); // 403 Forbidden
        }

        // Cambiar el estado a activo
        $informacionGeneral->estado = 'activo'; 
        $informacionGeneral->save(); 

        return response()->json(['message' => 'InformacionGeneral activado con éxito']);
    }
}
