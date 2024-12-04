<?php

namespace App\Http\Controllers\Api;

use App\Models\PuntoTuristico;
use App\Models\Parroquia;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


class PuntoTuristicoController extends Controller
{
    // Obtener todos los puntos turísticos
    public function index()
    {
        $puntosTuristicos = PuntoTuristico::orderBy('id', 'desc')->with('parroquia')->get();
    
        return response()->json([
            'cantidad' => $puntosTuristicos->count(),
            'datos' => $puntosTuristicos
            /*->map(function ($punto) {
                return [
                    'id' => $punto->id,
                    'nombre' => $punto->nombre,
                    'descripcion' => $punto->descripcion,
                    'latitud' => $punto->latitud,
                    'longitud' => $punto->longitud,
                    'id_parroquia' => $punto->id_parroquia,
                    'nombre_parroquia' => $punto->parroquia->nombre ?? null,
                    'estado' => $punto->estado,
                    'creado_por' => $punto->creado_por,
                    'editado_por' => $punto->editado_por,
                    'fecha_creacion' => $punto->fecha_creacion,
                    'fecha_ultima_edicion' => $punto->fecha_ultima_edicion,
                ];
            })*/
        ]);
    }
    

    // Mostrar un punto turístico por ID
    public function show($id)
    {
        $puntoTuristico = PuntoTuristico::with('parroquia')->find($id);

        if (!$puntoTuristico) {
            return response()->json(['message' => 'Punto turístico no encontrado'], 404);
        }

        return response()->json($puntoTuristico, 200);
    }

    // Obtener todos los puntos turísticos activos
    public function indexActivos()
    {
        $puntosActivos = PuntoTuristico::with('parroquia')->where('estado', 'activo')->orderBy('id', 'desc')->get();

        return response()->json([
            'cantidad' => $puntosActivos->count(),
            'datos' => $puntosActivos
        ], 200);
    }

    // Mostrar un punto turístico activo por ID
    public function showActivo(string $id)
    {
        $puntoTuristico = PuntoTuristico::with('parroquia')
                                        ->where('id', $id)
                                        ->where('estado', 'activo')
                                        ->first();
    
        if (!$puntoTuristico) {
            return response()->json(['message' => 'Punto turístico no encontrado o no está activo'], 404);
        }
    
        return response()->json($puntoTuristico, 200);
    }

    // Crear un nuevo punto turístico
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'latitud' => 'required|numeric',
            'longitud' => 'required|numeric',
            'id_parroquia' => 'required|integer|exists:parroquias,id',
            'creado_por' => 'required|string|max:255',
        ]);

        // Verificar duplicados
        if (PuntoTuristico::where([
            ['nombre', $request->nombre],
            ['id_parroquia', $request->id_parroquia],
            ['estado', 'activo']
        ])->exists()) {
            return response()->json(['message' => 'Ya existe un punto turístico activo con este nombre en la misma parroquia'], 409);
        }

        $validatedData['estado'] = 'activo';
        $puntoTuristico = PuntoTuristico::create($validatedData);

        return response()->json($puntoTuristico, 201);
    }

    // Actualizar un punto turístico
    public function update(Request $request, $id)
    {
        $puntoTuristico = PuntoTuristico::find($id);

        if (!$puntoTuristico) {
            return response()->json(['message' => 'Punto turístico no encontrado'], 404);
        }

        if ($puntoTuristico->estado === 'inactivo') {
            return response()->json(['message' => 'No se puede actualizar un punto turístico inactivo'], 409);
        }

        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'latitud' => 'nullable|numeric',
            'longitud' => 'nullable|numeric',
            'id_parroquia' => 'nullable|exists:parroquias,id',
            'editado_por' => 'required|string|max:255',
        ]);

        $puntoTuristico->update($validatedData);

        return response()->json($puntoTuristico, 200);
    }

    // Eliminar un punto turístico (cambiar estado a inactivo)
    public function destroy($id)
    {
        $puntoTuristico = PuntoTuristico::find($id);

        if (!$puntoTuristico) {
            return response()->json(['message' => 'Punto turístico no encontrado'], 404);
        }

        if ($puntoTuristico->estado === 'inactivo') {
            return response()->json(['message' => 'El punto turístico ya está inactivo'], 409);
        }

        $puntoTuristico->estado = 'inactivo';
        $puntoTuristico->save();

        return response()->json(['message' => 'El punto turístico fue marcado como inactivo'], 200);
    }

    public function activar(string $id)
    {
        $puntoTuristico = PuntoTuristico::find($id);

        if (!$puntoTuristico) {
            return response()->json(['message' => 'PuntoTuristico no encontrado'], 404);
        }

        if ($puntoTuristico->estado !== 'inactivo') {
            return response()->json(['message' => 'El PuntoTuristico ya se encuentra activo'], 403); // 403 Forbidden
        }

        // Cambiar el estado a activo
        $puntoTuristico->estado = 'activo'; 
        $puntoTuristico->save(); 

        return response()->json(['message' => 'PuntoTuristico activado con éxito']);
    }

    public function mostrarDataPuntoTuristico($id)
    {
        // Obtener el punto turístico
        $puntoTuristico = PuntoTuristico::select('id', 'nombre', 'descripcion', 'latitud', 'longitud', 'estado', 'id_parroquia')
            ->find($id);
        if (!$puntoTuristico) {
            return response()->json(['message' => 'Punto Turístico no encontrado'], 404);
        }

        // Obtener la parroquia relacionada con el punto turístico
        $parroquia = Parroquia::where('id', $puntoTuristico->id_parroquia)
            ->where('estado', 'activo')
            ->first();

        // Verificación si se encuentra o no la parroquia
        $nombreParroquia = $parroquia ? $parroquia->nombre : "No encontrada";

        // Retornar una respuesta con la información más limpia
        return response()->json([
            'PuntoTuristico' => $puntoTuristico,
            'Parroquia' => $nombreParroquia,
        ], 200);
    }

    public function buscarPorEtiqueta($id_etiqueta)
    {
        // Obtener todos los puntos turísticos que tienen la etiqueta especificada
        $puntosTuristicos = PuntoTuristico::whereHas('etiquetas', function ($query) use ($id_etiqueta) {
            $query->where('puntos_turisticos_etiqueta.id_etiqueta', $id_etiqueta);
        })->get();

        // Verificar si se encontraron puntos turísticos
        if ($puntosTuristicos->isEmpty()) {
            return response()->json(['message' => 'No se encontraron puntos turísticos para esta etiqueta'], 404);
        }

        // Retornar los puntos turísticos encontrados
        return response()->json($puntosTuristicos, 200);
    }


}
