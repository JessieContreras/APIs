<?php

namespace App\Http\Controllers\Api;

use App\Models\EstadoVia;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EstadoViaController extends Controller
{
    // Listar todos los estados de vías
    public function index()
    {
        return EstadoVia::all();
    }

    // Mostrar un estado de vía específico
    public function show($id)
    {
        $estadoVia = EstadoVia::find($id);

        if (!$estadoVia) {
            return response()->json(['message' => 'Estado de vía no encontrado'], 404);
        }

        return response()->json($estadoVia, 200);
    }

    // Mostrar todas las vias no eliminadas
    public function indexActivos()
    {
        return EstadoVia::where('Eliminado', 'no')->get(); // Filtra solo las vias no eliminadas
    }

    // Buscar un Estado Via por ID solo si está activo
    public function showActivo(string $id)
    {
        $estadoVia = EstadoVia::where('id', $id)->where('Eliminado', 'no')->first();

        if (!$estadoVia) {
            return response()->json(['message' => 'Estado via no encontrado'], 404);
        }

        return response()->json($estadoVia);
    }

    // Crear un nuevo estado de vía
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nombre_via' => 'required|string|max:255',
            'estado' => 'required|string|max:255',
            'comentarios' => 'nullable|string',
            'creado_por' => 'required|string|max:255',
        ]);

        // Verificar si ya existe un estado vía con el mismo nombre
        $estadoViaExistente = EstadoVia::where('nombre_via', $request->nombre_via)->where('eliminado', 'no')->first(); 

        if ($estadoViaExistente) {
            // Si existe, retornar un error
            return response()->json([
                'message' => 'La vía ya existe. Se recomienda cambiar el estado o crear una nueva vía.'
            ], 409);
        }

        // Agregar 'eliminado' con valor 'no' al array de datos
        $validatedData['eliminado'] = 'no';

        $estadoVia = EstadoVia::create($validatedData);

        return response()->json($estadoVia, 201);
    }


    // Actualizar un estado de vía existente
    public function update(Request $request, $id)
    {
        // Buscar el estado de vía por ID
        $estadoVia = EstadoVia::find($id);

        if (!$estadoVia) {
            return response()->json(['message' => 'Estado de vía no encontrado'], 404);
        }

        // Verificar si el estado de vía ya fue eliminado (eliminado = 'si')
        if ($estadoVia->eliminado === 'si') {
            return response()->json([
                'message' => 'La vía ha sido eliminada y no se puede actualizar'
            ], 409);
        }

        // Validar los datos
        $validatedData = $request->validate([
            'estado' => 'sometimes|string|max:255',
            'comentarios' => 'nullable|string',
            'editado_por' => 'required|string|max:255',
        ]);

        // Actualizar los datos del estado de vía
        $estadoVia->update($validatedData);

        // Retornar una respuesta exitosa
        return response()->json($estadoVia, 200);
    }


   // Eliminar (cambiar a eliminado) un estado de vía
    public function destroy(string $id)
    {
        // Buscar el estado de vía por ID
        $estadoVia = EstadoVia::find($id);

        if (!$estadoVia) {
            return response()->json(['message' => 'Estado de vía no encontrado'], 404);
        }

        // Verificar si ya está eliminado
        if ($estadoVia->eliminado === 'si') {
            return response()->json(['message' => 'Este estado de vía ya ha sido eliminado'], 409); // 409 Conflict
        }

        // Cambiar el estado a eliminado
        $estadoVia->eliminado = 'si'; 
        $estadoVia->save(); 

        return response()->json(['message' => 'Estado de vía eliminado con éxito'], 200);
    }

}
