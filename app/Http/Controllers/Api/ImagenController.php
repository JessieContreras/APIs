<?php

namespace App\Http\Controllers\Api;

use App\Models\Imagen;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ImagenController extends Controller
{
    // Listar todos los imagenes
    public function index()
    {
        $imagenes = Imagen::orderBy('id', 'desc')->get();
        return response()->json([
            'cantidad' => $imagenes->count(),
            'datos' => $imagenes
        ], 200);
    }

    // Mostrar un imagen específico
    public function show($id)
    {
        $imagen = Imagen::find($id);

        if (!$imagen) {
            return response()->json(['message' => 'Imagen no encontrado'], 404);
        }

        return response()->json($imagen, 200);
    }

     
    public function indexActivos()
    {
        $activos = Imagen::where('estado', 'activo')->orderBy('id', 'desc')->get();
        return response()->json([
            'cantidad' => $activos->count(),
            'datos' => $activos
        ], 200);
    }
 
     
     public function showActivo(string $id)
     {
         $imagen = Imagen::where('id', $id)->where('estado', 'inactivo')->first();
 
         if (!$imagen) {
             return response()->json(['message' => 'Imagen no encontrado'], 404);
         }
 
         return response()->json($imagen);
     }

    // Crear un nuevo imagen
    public function store(Request $request)
    {
        // Validar los datos recibidos
        $validated = $request->validate([
            'id_entidad' => 'required|integer',
            'tipo' => 'required|string|max:255',
            'ruta_imagen' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'estado' => 'required|string|max:20',
            'creado_por' => 'nullable|string|max:255',
        ]);

        // Crear una nueva imagen con los datos validados
        $imagen = Imagen::create(array_merge($validated, [
            'fecha_creacion' => now(),
        ]));

        // Devolver la respuesta con el objeto creado
        return response()->json(['message' => 'Imagen creada con éxito', 'imagen' => $imagen], 201);
    }


    // Actualizar un imagen existente
    public function update(Request $request, $id)
    {
        // Buscar la imagen por su ID
        $imagen = Imagen::find($id);

        // Verificar si la imagen no existe
        if (!$imagen) {
            return response()->json(['message' => 'Imagen no encontrada'], 404);
        }

        // Validar los datos recibidos
        $validated = $request->validate([
            'id_entidad' => 'sometimes|integer',
            'tipo' => 'sometimes|string|max:255',
            'ruta_imagen' => 'sometimes|string|max:255',
            'descripcion' => 'sometimes|string',
            'estado' => 'sometimes|string|max:20',
            'editado_por' => 'nullable|string|max:255',
        ]);

        // Actualizar los campos de la imagen con los datos validados
        $imagen->update(array_merge($validated, [
            'fecha_ultima_edicion' => now(),
        ]));

        // Devolver la respuesta con la imagen actualizada
        return response()->json(['message' => 'Imagen actualizada con éxito', 'imagen' => $imagen], 200);
    }


    // Desactivar (eliminar lógico) un imagen
    public function destroy($id)
    {
        $imagen = Imagen::find($id);

        if (!$imagen) {
            return response()->json(['message' => 'Imagen no encontrado'], 404);
        }

        $imagen->update([
            'estado' => 'inactivo',
            'fecha_ultima_edicion' => now(),
        ]);

        return response()->json(['message' => 'Imagen desactivado con éxito'], 200);
    }

    public function activar(string $id)
    {
        $imagen = Imagen::find($id);

        if (!$imagen) {
            return response()->json(['message' => 'Imagen no encontrado'], 404);
        }

        if ($imagen->estado !== 'inactivo') {
            return response()->json(['message' => 'El Imagen ya se encuentra activo'], 403); // 403 Forbidden
        }

        // Cambiar el estado a activo
        $imagen->estado = 'activo'; 
        $imagen->save(); 

        return response()->json(['message' => 'Imagen activado con éxito']);
    }

    
}
