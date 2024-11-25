<?php

namespace App\Http\Controllers\Api;

use App\Models\PuntoTuristicoEtiqueta;
use App\Models\PuntoTuristico;
use App\Models\EtiquetaTuristica;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PuntoTuristicoEtiquetaController extends Controller
{
    // Listar todas las etiquetas asignadas a puntos turísticos
    public function index()
    {
        $puntoTuristicoEtiqueta = PuntoTuristicoEtiqueta::all();
        return response()->json([
            'cantidad' => $puntoTuristicoEtiqueta->count(),
            'datos' => $puntoTuristicoEtiqueta
        ], 200);
    }

    // Mostrar una asignación específica
    public function show(string $id_punto_turistico, string $id_etiqueta)
    {
        $asignacion = PuntoTuristicoEtiqueta::where('id_punto_turistico', $id_punto_turistico)
                                            ->where('id_etiqueta', $id_etiqueta)
                                            ->first();

        if (!$asignacion) {
            return response()->json(['message' => 'Asignación no encontrada'], 404);
        }

        $punto = PuntoTuristico::find($asignacion->id_punto_turistico);
        $etiqueta = EtiquetaTuristica::find($asignacion->id_etiqueta);

        return response()->json([
            'asignacion' => $asignacion,
            'punto_turistico' => $punto->nombre ?? null,
            'etiqueta' => $etiqueta->nombre ?? null,
        ], 200);
    }

    // Listar asignaciones activas
    public function indexActivos()
    {
        $activos = PuntoTuristicoEtiqueta::where('estado', 'activo')->get();
        return response()->json([
            'cantidad' => $activos->count(),
            'datos' => $activos
        ], 200);
    }

    // Crear una nueva asignación
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'id_punto_turistico' => 'required|integer|exists:puntos_turisticos,id',
            'id_etiqueta' => 'required|integer|exists:etiquetas_turisticas,id',
            'creado_por' => 'required|string|max:255',
        ]);

        $validatedData['estado'] = 'activo';

        $asignacion = PuntoTuristicoEtiqueta::create($validatedData);

        return response()->json($asignacion, 201);
    }

    // Actualizar una asignación existente
    public function update(Request $request, string $id_punto_turistico, string $id_etiqueta)
    {
        $asignacion = PuntoTuristicoEtiqueta::where('id_punto_turistico', $id_punto_turistico)
                                            ->where('id_etiqueta', $id_etiqueta)
                                            ->first();

        if (!$asignacion) {
            return response()->json(['message' => 'Asignación no encontrada'], 404);
        }

        if ($asignacion->estado === 'inactivo') {
            return response()->json(['message' => 'La asignación está inactiva y no puede ser actualizada'], 409);
        }

        $validatedData = $request->validate([
            'id_etiqueta' => 'nullable|integer|exists:etiquetas_turisticas,id',
            'editado_por' => 'required|string|max:255',
        ]);

        $asignacion->update($validatedData);

        return response()->json($asignacion, 200);
    }

    // Eliminar una asignación (cambiar estado a inactivo)
    public function destroy(string $id_punto_turistico, string $id_etiqueta)
    {
        $asignacion = PuntoTuristicoEtiqueta::where('id_punto_turistico', $id_punto_turistico)
                                            ->where('id_etiqueta', $id_etiqueta)
                                            ->first();

        if (!$asignacion) {
            return response()->json(['message' => 'Asignación no encontrada'], 404);
        }

        if ($asignacion->estado === 'inactivo') {
            return response()->json(['message' => 'La asignación ya está inactiva'], 409);
        }

        $asignacion->estado = 'inactivo';
        $asignacion->save();

        return response()->json(['message' => 'Asignación desactivada con éxito'], 200);
    }
}
