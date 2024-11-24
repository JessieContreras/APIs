<?php

namespace App\Http\Controllers;

use App\Models\Anuncio;
use Illuminate\Http\Request;

class AnuncioController extends Controller
{
    // Listar todos los anuncios
    public function index()
    {
        $anuncios = Anuncio::all();
        return response()->json($anuncios, 200);
    }

    // Mostrar un anuncio específico
    public function show($id)
    {
        $anuncio = Anuncio::find($id);

        if (!$anuncio) {
            return response()->json(['message' => 'Anuncio no encontrado'], 404);
        }

        return response()->json($anuncio, 200);
    }

     
     public function indexActivos()
     {
         return Anuncio::where('estado', 'inactivo')->get(); 
     }
 
     
     public function showActivo(string $id)
     {
         $anuncio = Anuncio::where('id', $id)->where('estado', 'inactivo')->first();
 
         if (!$anuncio) {
             return response()->json(['message' => 'Anuncio no encontrado'], 404);
         }
 
         return response()->json($anuncio);
     }

    // Crear un nuevo anuncio
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'estado' => 'required|string|max:20',
            'creado_por' => 'nullable|string|max:255',
        ]);

        $anuncio = Anuncio::create(array_merge($validated, [
            'fecha_creacion' => now(),
            'fecha_ultima_edicion' => now(),
        ]));

        return response()->json(['message' => 'Anuncio creado con éxito', 'anuncio' => $anuncio], 201);
    }

    // Actualizar un anuncio existente
    public function update(Request $request, $id)
    {
        $anuncio = Anuncio::find($id);

        if (!$anuncio) {
            return response()->json(['message' => 'Anuncio no encontrado'], 404);
        }

        $validated = $request->validate([
            'nombre' => 'sometimes|string|max:255',
            'descripcion' => 'sometimes|string',
            'estado' => 'sometimes|string|max:20',
            'editado_por' => 'nullable|string|max:255',
        ]);

        $anuncio->update(array_merge($validated, [
            'fecha_ultima_edicion' => now(),
        ]));

        return response()->json(['message' => 'Anuncio actualizado con éxito', 'anuncio' => $anuncio], 200);
    }

    // Desactivar (eliminar lógico) un anuncio
    public function destroy($id)
    {
        $anuncio = Anuncio::find($id);

        if (!$anuncio) {
            return response()->json(['message' => 'Anuncio no encontrado'], 404);
        }

        $anuncio->update([
            'estado' => 'inactivo',
            'fecha_ultima_edicion' => now(),
        ]);

        return response()->json(['message' => 'Anuncio desactivado con éxito'], 200);
    }

    
}
