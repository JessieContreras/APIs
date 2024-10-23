<?php

namespace App\Http\Controllers\Api;

use App\Models\Parroquia;
use App\Models\PuntoTuristico;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PuntoTuristicoController extends Controller
{
    public function index()
    {
        // Cargar los puntos turísticos junto con la información de la parroquia
        $puntosTuristicos = PuntoTuristico::with('parroquia')->get();
        
        // Mapear los datos para incluir el nombre de la parroquia
        return response()->json($puntosTuristicos->map(function ($punto) {
            return [
                'id' => $punto->id,
                'nombre' => $punto->nombre,
                'descripcion' => $punto->descripcion,
                'latitud' => $punto->latitud,
                'longitud' => $punto->longitud,
                'id_parroquia' => $punto->id_parroquia,
                'nombre_parroquia' => $punto->parroquia->nombre ?? null, // Obtener el nombre de la parroquia
                'estado' => $punto->estado,
                'creado_por' => $punto->creado_por,
                'editado_por' => $punto->editado_por,
                'fecha_creacion' => $punto->fecha_creacion,
                'fecha_ultima_edicion' => $punto->fecha_ultima_edicion,
            ];
        }), 200);
    }

    public function show($id)
    {
        $puntoTuristico = PuntoTuristico::with('parroquia')->find($id);

        if (!$puntoTuristico) {
            return response()->json(['message' => 'Punto turístico no encontrado'], 404);
        }

        return response()->json([
            'id' => $puntoTuristico->id,
            'nombre' => $puntoTuristico->nombre,
            'descripcion' => $puntoTuristico->descripcion,
            'latitud' => $puntoTuristico->latitud,
            'longitud' => $puntoTuristico->longitud,
            'id_parroquia' => $puntoTuristico->id_parroquia,
            'nombre_parroquia' => $puntoTuristico->parroquia->nombre ?? null, // Obtener el nombre de la parroquia
            'estado' => $puntoTuristico->estado,
            'creado_por' => $puntoTuristico->creado_por,
            'editado_por' => $puntoTuristico->editado_por,
            'fecha_creacion' => $puntoTuristico->fecha_creacion,
            'fecha_ultima_edicion' => $puntoTuristico->fecha_ultima_edicion,
        ], 200);
    }

    public function indexActivos()
    {
        // Cargar los puntos turísticos activos junto con la información de la parroquia
        $puntosTuristicosActivos = PuntoTuristico::with('parroquia')->where('estado', 'activo')->get();
        
        // Mapear los datos para incluir el nombre de la parroquia
        return response()->json($puntosTuristicosActivos->map(function ($punto) {
            return [
                'id' => $punto->id,
                'nombre' => $punto->nombre,
                'descripcion' => $punto->descripcion,
                'latitud' => $punto->latitud,
                'longitud' => $punto->longitud,
                'id_parroquia' => $punto->id_parroquia,
                'nombre_parroquia' => $punto->parroquia->nombre ?? null, // Obtener el nombre de la parroquia
                'estado' => $punto->estado,
                'creado_por' => $punto->creado_por,
                'editado_por' => $punto->editado_por,
                'fecha_creacion' => $punto->fecha_creacion,
                'fecha_ultima_edicion' => $punto->fecha_ultima_edicion,
            ];
        }), 200);
    }
    

    public function showActivo(string $id)
    {
        $puntoTuristico = PuntoTuristico::with('parroquia')->where('id', $id)->where('estado', 'activo')->first();

        if (!$puntoTuristico) {
            return response()->json(['message' => 'Punto turístico no encontrado o no está activo'], 404);
        }

        return response()->json([
            'id' => $puntoTuristico->id,
            'nombre' => $puntoTuristico->nombre,
            'descripcion' => $puntoTuristico->descripcion,
            'latitud' => $puntoTuristico->latitud,
            'longitud' => $puntoTuristico->longitud,
            'id_parroquia' => $puntoTuristico->id_parroquia,
            'nombre_parroquia' => $puntoTuristico->parroquia->nombre ?? null, // Obtener el nombre de la parroquia
            'estado' => $puntoTuristico->estado,
            'creado_por' => $puntoTuristico->creado_por,
            'editado_por' => $puntoTuristico->editado_por,
            'fecha_creacion' => $puntoTuristico->fecha_creacion,
            'fecha_ultima_edicion' => $puntoTuristico->fecha_ultima_edicion,
        ], 200);
    }


    public function store(Request $request)
    {
        // Validar los datos de entrada
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'latitud' => 'required|numeric',
            'longitud' => 'required|numeric',
            'id_parroquia' => 'required|integer',
            'creado_por' => 'required|string|max:255',
        ]);
    
        // Verificar si ya existe un punto turístico con el mismo nombre y parroquia
        $puntoExistente = PuntoTuristico::where('nombre', $request->nombre)
            ->where('id_parroquia', $request->id_parroquia)
            ->where('estado', 'activo')
            ->first();
    
        if ($puntoExistente) {
            // Si existe, retornar un error
            return response()->json([
                'message' => 'El punto turístico ya existe. Se recomienda cambiar el estado o crear un nuevo punto con otro nombre.',
                'estado_actual' => $puntoExistente->estado // Información adicional
            ], 409);
        }
    
        // Establecer el estado activo y las fechas
        $validatedData['estado'] = 'activo';
    
        // Crear el nuevo punto turístico
        $puntoTuristico = PuntoTuristico::create($validatedData);
    
        return response()->json($puntoTuristico, 201);
    }
    
    public function update(Request $request, string $id)
    {
        $puntoTuristico = PuntoTuristico::find($id);

        if (!$puntoTuristico) {
            return response()->json(['message' => 'Punto turístico no encontrado'], 404);
        }

        // Verificar si el punto turístico está inactivo
        if ($puntoTuristico->estado === 'inactivo') {
            return response()->json(['message' => 'El punto turístico está inactivo y no se puede actualizar.'], 409);
        }

        // Validar los datos de entrada
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'latitud' => 'nullable|numeric',
            'longitud' => 'nullable|numeric',
            'id_parroquia' => 'nullable|exists:parroquias,id',
            'editado_por' => 'required|string|max:255',
        ]);

        // Verificar si ya existe otro punto con el mismo nombre (sin tomar en cuenta el actual)
        $puntoExistente = PuntoTuristico::where('nombre', $request->nombre)
                                        ->where('estado', 'activo')
                                        ->where('id', '!=', $id)
                                        ->first(); 

        if ($puntoExistente) {
            // Si existe, retornar un error
            return response()->json([
                'message' => 'Ya existe otro punto turístico con este nombre. Por favor, elija otro nombre.'
            ], 409);
        }

        // Actualizar el punto turístico
        $puntoTuristico->update($validatedData);

        return response()->json($puntoTuristico, 200);
    }

    // Cambiar el estado de un punto turístico a inactivo
    public function destroy(string $id)
    {
        $puntoTuristico = PuntoTuristico::find($id);

        if (!$puntoTuristico) {
            return response()->json(['message' => 'Punto turístico no encontrado'], 404);
        }

        // Verificar si el punto ya está inactivo
        if ($puntoTuristico->estado === 'inactivo') {
            return response()->json(['message' => 'Este punto turístico ya ha sido eliminado'], 409);
        }

        // Cambiar el estado a inactivo
        $puntoTuristico->estado = 'inactivo';
        $puntoTuristico->save();

        return response()->json(['message' => 'Punto turístico eliminado con éxito'], 200);
    }
}
