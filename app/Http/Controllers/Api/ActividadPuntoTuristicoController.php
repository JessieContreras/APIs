<?php

namespace App\Http\Controllers\Api;

use App\Models\ActividadPuntoTuristico;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ActividadPuntoTuristicoController extends Controller
{
    public function index()
    {
        $actividadPuntoTuristico = ActividadPuntoTuristico::orderBy('id', 'desc')->get(); 
        return response()->json([
            'cantidad' => $actividadPuntoTuristico->count(),
            'datos' => $actividadPuntoTuristico
        ], 200);
    }

    public function show($id)
    {
        $actividadPuntoTuristico = ActividadPuntoTuristico::find($id);

        if (!$actividadPuntoTuristico) {
            return response()->json(['message' => 'ActividadPuntoTuristico no encontrado'], 404);
        }

        return response()->json($actividadPuntoTuristico, 200);
    }

    public function indexActivos()
    {
        $activos = ActividadPuntoTuristico::where('estado', 'activo')->orderBy('id', 'desc')->get();
        return response()->json([
            'cantidad' => $activos->count(),
            'datos' => $activos
        ], 200);
    }

    public function showActivo(string $id)
    {
        $actividadPuntoTuristico = ActividadPuntoTuristico::where('id', $id)->where('estado', 'activo')->first();
    
        if (!$actividadPuntoTuristico) {
            return response()->json(['message' => 'ActividadPuntoTuristico no encontrado o no está activo'], 404);
        }
    
        return response()->json($actividadPuntoTuristico, 200);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'id_punto_turistico' => 'required|integer',
            'actividad' => 'required|string|max:255',
            'precio' => 'numeric',
            'tipo' => 'required|string|max:50',
            'creado_por' => 'required|string|max:255',
        ]);

        $servicioExistente = ActividadPuntoTuristico::where('id_punto_turistico', $request->id_punto_turistico)
                                          ->where('actividad', $request->actividad)
                                          ->where('estado', 'activo')
                                          ->first();

        if ($servicioExistente) {
            return response()->json(['message' => 'Ya existe una actividad activo con este nombre para este local.'], 409);
        }

        $validatedData['estado'] = 'activo';

        $actividadPuntoTuristico = ActividadPuntoTuristico::create($validatedData);

        return response()->json($actividadPuntoTuristico, 201);
    }

    public function update(Request $request, string $id)
    {
        $actividadPuntoTuristico = ActividadPuntoTuristico::find($id);

        if (!$actividadPuntoTuristico) {
            return response()->json(['message' => 'ActividadPuntoTuristico no encontrado'], 404);
        }

        if ($actividadPuntoTuristico->estado === 'inactivo') {
            return response()->json(['message' => 'La actividad está inactivo y no se puede actualizar.'], 409);
        }

        $validatedData = $request->validate([
            'id_punto_turistico' => 'required|integer',
            'actividad' => 'required|string|max:255',
            'precio' => 'numeric',
            'tipo' => 'required|string|max:50',
            'editado_por' => 'required|string|max:255',
        ]);

        $servicioExistente = ActividadPuntoTuristico::where('id_punto_turistico', $request->id_punto_turistico)
                                          ->where('actividad', $request->actividad)
                                          ->where('estado', 'activo')
                                          ->where('id', '!=', $id)
                                          ->first();

        if ($servicioExistente) {
            return response()->json(['message' => 'Ya existe otra actividad activo con este nombre para este local.'], 409);
        }

        $actividadPuntoTuristico->update($validatedData);

        return response()->json($actividadPuntoTuristico, 200);
    }

    public function destroy(string $id)
    {
        $actividadPuntoTuristico = ActividadPuntoTuristico::find($id);

        if (!$actividadPuntoTuristico) {
            return response()->json(['message' => 'ActividadPuntoTuristico no encontrado'], 404);
        }

        if ($actividadPuntoTuristico->estado === 'inactivo') {
            return response()->json(['message' => 'Esta actividad ya ha sido eliminado'], 409);
        }

        $actividadPuntoTuristico->estado = 'inactivo';
        $actividadPuntoTuristico->save();

        return response()->json(['message' => 'ActividadPuntoTuristico eliminado con éxito'], 200);
    }

    public function activar(string $id)
    {
        $actividadPuntoTuristico = ActividadPuntoTuristico::find($id);

        if (!$actividadPuntoTuristico) {
            return response()->json(['message' => 'ActividadPuntoTuristico no encontrado'], 404);
        }

        if ($actividadPuntoTuristico->estado !== 'inactivo') {
            return response()->json(['message' => 'El ActividadPuntoTuristico ya se encuentra activo'], 403); // 403 Forbidden
        }

        // Cambiar el estado a activo
        $actividadPuntoTuristico->estado = 'activo'; 
        $actividadPuntoTuristico->save(); 

        return response()->json(['message' => 'ActividadPuntoTuristico activado con éxito']);
    }

    public function ActividadPorIDPunto($id)
    {
        // Obtener los servicios activos para el local dado
        $actividadPuntoTuristico = ActividadPuntoTuristico::where('id_punto_turistico', $id)->get();

        // Verificar si la colección está vacía
        if ($actividadPuntoTuristico->isEmpty()) {
            return response()->json(['message' => 'El punto turístico no tiene actividades activas o no fue encontrado'], 404);
        }

        // Retornar la respuesta con los datos
        return response()->json([
            'cantidad' => $actividadPuntoTuristico->count(),
            'datos' => $actividadPuntoTuristico
        ], 200);
    }
}
