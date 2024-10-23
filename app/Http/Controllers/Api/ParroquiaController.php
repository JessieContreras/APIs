<?php

namespace App\Http\Controllers\Api;

use App\Models\Parroquia;
use App\Models\PuntoTuristico;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ParroquiaController extends Controller
{
    public function index()
    {
        return Parroquia::all();
    }

    public function show($id)
    {
        $parroquia = Parroquia::find($id);

        if (!$parroquia) {
            return response()->json(['message' => 'Parroquia no encontrada'], 404);
        }

        return response()->json($parroquia, 200);
    }

    public function indexActivos()
    {
        return response()->json(Parroquia::where('estado', 'activo')->get(), 200);
    }

    public function showActivo(string $id)
    {
        $parroquia = Parroquia::where('id', $id)->where('estado', 'activo')->first();
    
        if (!$parroquia) {
            return response()->json(['message' => 'Parroquia no encontrada o no está activo'], 404);
        }
    
        return response()->json($parroquia, 200);
    }

    public function store(Request $request)
    {
        // Validar los datos de entrada
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'fecha_fundacion' => 'required|string|max:255',
            'poblacion' => 'required|string|max:255',
            'temperatura_promedio' => 'required|string|max:255',
            'creado_por' => 'required|string|max:255',
        ]);

        // Verificar si ya existe un estado vía con el mismo nombre
        $parroquiaExistente = Parroquia::where('nombre', $request->nombre)->where('estado', 'activo')->first(); 

        if ($parroquiaExistente) {
            // Si existe, retornar un error
            return response()->json([
                'message' => 'La parroquia ya existe. Se recomienda cambiar el estado o crear una nueva parroquia con otro nombre.'
            ], 409);
        }

        // Establecer el estado activo y las fechas
        $validatedData['estado'] = 'activo';

        // Crear la nueva parroquia
        $parroquia = Parroquia::create($validatedData);

        return response()->json($parroquia, 201);
    }

    public function update(Request $request, string $id)
    {
        $parroquia = Parroquia::find($id);

        if (!$parroquia) {
            return response()->json(['message' => 'Parroquia no encontrada'], 404);
        }

        // Verificar si la parroquia está inactiva
        if ($parroquia->estado === 'inactivo') {
            return response()->json(['message' => 'La parroquia está inactiva y no se puede actualizar.'], 409);
        }

        // Validar los datos de entrada
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'fecha_fundacion' => 'required|string|max:255',
            'poblacion' => 'required|string|max:255',
            'temperatura_promedio' => 'required|string|max:255',
            'editado_por' => 'required|string|max:255',
        ]);

        // Verificar si ya existe una parroquia con el mismo nombre (exceptuando la actual)
        $parroquiaExistente = Parroquia::where('nombre', $request->nombre)
                                        ->where('estado', 'activo')
                                        ->where('id', '!=', $id)
                                        ->first(); 

        if ($parroquiaExistente) {
            // Si existe, retornar un error
            return response()->json([
                'message' => 'Ya existe otra parroquia con este nombre. Por favor, elija otro nombre.'
            ], 409);
        }

        // Actualizar la parroquia
        $parroquia->update($validatedData);

        return response()->json($parroquia, 200);
    }


    // Cambiar el estado de una parroquia a inactiva
    public function destroy(string $id)
    {
        $parroquia = Parroquia::find($id);

        if (!$parroquia) {
            return response()->json(['message' => 'Parroquia no encontrada'], 404);
        }

        // Verificar si la parroquia ya está inactiva
        if ($parroquia->estado === 'inactivo') {
            return response()->json(['message' => 'Esta parroquia ya ha sido eliminada'], 409);
        }

        // Cambiar el estado a inactivo
        $parroquia->estado = 'inactivo';
        $parroquia->save();

        return response()->json(['message' => 'Parroquia eliminada con éxito'], 200);
    }

    public function puntoTuristicoParroquia(string $id)
    {
        // Buscar la parroquia por ID
        $parroquia = Parroquia::find($id);

        if (!$parroquia) {
            return response()->json(['message' => 'Parroquia no encontrada'], 404);
        }

        // Obtener los puntos turísticos activos relacionados con la parroquia
        $puntosTuristicos = PuntoTuristico::select('nombre', 'descripcion', 'latitud', 'longitud')
            ->where('id_parroquia', $id)
            ->where('estado', 'activo')
            ->get();

        return response()->json([
            'parroquia' => $parroquia->nombre,
            'puntos_turisticos' => $puntosTuristicos
        ], 200);
    }

}
