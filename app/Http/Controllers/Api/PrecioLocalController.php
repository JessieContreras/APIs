<?php

namespace App\Http\Controllers\Api;

use App\Models\PrecioLocal;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PrecioLocalController extends Controller
{
    public function index()
    {
        return PrecioLocal::all();
    }

    public function show($id)
    {
        $precioLocal = PrecioLocal::find($id);

        if (!$precioLocal) {
            return response()->json(['message' => 'PrecioLocal no encontrado'], 404);
        }

        return response()->json($precioLocal, 200);
    }

    public function indexActivos()
    {
        return response()->json(PrecioLocal::where('estado', 'activo')->get(), 200);
    }

    public function showActivo(string $id)
    {
        $precioLocal = PrecioLocal::where('id', $id)->where('estado', 'activo')->first();
    
        if (!$precioLocal) {
            return response()->json(['message' => 'PrecioLocal no encontrado o no está activo'], 404);
        }
    
        return response()->json($precioLocal, 200);
    }

    public function store(Request $request)
    {
        // Validar los datos de entrada
        $validatedData = $request->validate([
            'id_local' => 'required|integer',
            'servicio' => 'required|string|max:255',
            'precio' => 'required|numeric',
            'creado_por' => 'required|string|max:255',
        ]);

        // Verificar si ya existe un precio activo para el mismo servicio y local
        $precioExistente = PrecioLocal::where('id_local', $request->id_local)
                                      ->where('servicio', $request->servicio)
                                      ->where('estado', 'activo')
                                      ->first();

        if ($precioExistente) {
            return response()->json([
                'message' => 'Ya existe un precio activo para este servicio en este local.'
            ], 409);
        }

        // Establecer el estado activo
        $validatedData['estado'] = 'activo';

        // Crear el nuevo precio local
        $precioLocal = PrecioLocal::create($validatedData);

        return response()->json($precioLocal, 201);
    }

    public function update(Request $request, string $id)
    {
        $precioLocal = PrecioLocal::find($id);

        if (!$precioLocal) {
            return response()->json(['message' => 'PrecioLocal no encontrado'], 404);
        }

        // Verificar si el precio está inactivo
        if ($precioLocal->estado === 'inactivo') {
            return response()->json(['message' => 'El precio está inactivo y no se puede actualizar.'], 409);
        }

        // Validar los datos de entrada
        $validatedData = $request->validate([
            'id_local' => 'required|integer',
            'servicio' => 'required|string|max:255',
            'precio' => 'required|numeric',
            'editado_por' => 'required|string|max:255',
        ]);

        // Verificar si ya existe otro precio activo para el mismo servicio y local
        $precioExistente = PrecioLocal::where('id_local', $request->id_local)
                                      ->where('servicio', $request->servicio)
                                      ->where('estado', 'activo')
                                      ->where('id', '!=', $id)
                                      ->first();

        if ($precioExistente) {
            return response()->json([
                'message' => 'Ya existe otro precio activo para este servicio en este local.'
            ], 409);
        }

        // Actualizar el precio local
        $precioLocal->update($validatedData);

        return response()->json($precioLocal, 200);
    }

    public function destroy(string $id)
    {
        $precioLocal = PrecioLocal::find($id);

        if (!$precioLocal) {
            return response()->json(['message' => 'PrecioLocal no encontrado'], 404);
        }

        // Verificar si ya está inactivo
        if ($precioLocal->estado === 'inactivo') {
            return response()->json(['message' => 'Este precio ya ha sido eliminado'], 409);
        }

        // Cambiar el estado a inactivo
        $precioLocal->estado = 'inactivo';
        $precioLocal->save();

        return response()->json(['message' => 'PrecioLocal eliminado con éxito'], 200);
    }
}
