<?php

namespace App\Http\Controllers\Api;

use App\Models\LocalTuristico;
use App\Models\DuenoLocal;
use App\Models\HorarioAtencion;
use App\Models\PrecioLocal;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LocalTuristicoController extends Controller
{
    public function index()
    {
        return LocalTuristico::all();
    }

    public function show($id)
    {
        $localTuristico = LocalTuristico::find($id);

        if (!$localTuristico) {
            return response()->json(['message' => 'Local Turistico no encontrada'], 404);
        }

        return response()->json($localTuristico, 200);
    }

    public function indexActivos()
    {
        return response()->json(LocalTuristico::where('estado', 'activo')->get(), 200);
    }

    public function showActivo(string $id)
    {
        $localTuristico = LocalTuristico::where('id', $id)->where('estado', 'activo')->first();
    
        if (!$localTuristico) {
            return response()->json(['message' => 'Local Turistico no encontrada o no está activo'], 404);
        }
    
        return response()->json($localTuristico, 200);
    }

    public function store(Request $request)
    {
        // Validar los datos de entrada
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'id_dueno' => 'required|integer',
            'direccion' => 'required|string|max:255',
            'latitud' => 'required|numeric',
            'longitud' => 'required|numeric',
            'creado_por' => 'required|string|max:255',
        ]);

        // Verificar si ya existe un local con el mismo nombre para el mismo dueño
        $existeLocalConMismoNombre = LocalTuristico::where('id_dueno', $validatedData['id_dueno'])
            ->where('nombre', $validatedData['nombre'])
            ->exists();

        if ($existeLocalConMismoNombre) {
            return response()->json(['error' => 'El dueño ya tiene un local con este nombre.'], 422);
        }

        // Verificar si ya existe un local en la misma ubicación (latitud y longitud)
        $existeLocalEnMismaUbicacion = LocalTuristico::where('latitud', $validatedData['latitud'])
            ->where('longitud', $validatedData['longitud'])
            ->exists();

        if ($existeLocalEnMismaUbicacion) {
            return response()->json(['error' => 'Ya existe un local en esta ubicación.'], 422);
        }

        // Establecer el estado 'activo' y las fechas de creación/edición
        $validatedData['estado'] = 'activo';

        // Crear el nuevo registro en la tabla `locales_turisticos`
        $localTuristico = LocalTuristico::create($validatedData);

        // Retornar el local turístico creado con el código de respuesta 201 (creado)
        return response()->json($localTuristico, 201);
    }


    // Actualizar un local turístico existente
    public function update(Request $request, $id)
    {
        // Buscar el local turístico por ID
        $localTuristico = LocalTuristico::find($id);

        // Verificar si el local turístico existe
        if (!$localTuristico) {
            return response()->json(['message' => 'Local turístico no encontrado'], 404);
        }

        // Verificar si el local ya fue eliminado (estado = 'inactivo')
        if ($localTuristico->estado === 'inactivo') {
            return response()->json([
                'message' => 'El local turístico está inactivo y no se puede actualizar.'
            ], 409);
        }

        // Validar los datos de entrada, utilizando "sometimes" para campos opcionales
        $validatedData = $request->validate([
            'nombre' => 'sometimes|string|max:255',
            'descripcion' => 'nullable|string',
            'id_dueno' => 'sometimes|integer',
            'direccion' => 'sometimes|string|max:255',
            'latitud' => 'sometimes|numeric',
            'longitud' => 'sometimes|numeric',
            'editado_por' => 'required|string|max:255',
        ]);

        // Validar si se actualiza el nombre del local y pertenece al mismo dueño
        if (isset($validatedData['nombre']) && isset($validatedData['id_dueno'])) {
            $existeLocalConMismoNombre = LocalTuristico::where('id_dueno', $validatedData['id_dueno'])
                ->where('nombre', $validatedData['nombre'])
                ->where('id', '!=', $id) // Ignorar el local actual
                ->exists();

            if ($existeLocalConMismoNombre) {
                return response()->json(['error' => 'El dueño ya tiene un local con este nombre.'], 422);
            }
        }

        // Validar si se actualizan las coordenadas de ubicación
        if (isset($validatedData['latitud']) && isset($validatedData['longitud'])) {
            $existeLocalEnMismaUbicacion = LocalTuristico::where('latitud', $validatedData['latitud'])
                ->where('longitud', $validatedData['longitud'])
                ->where('id', '!=', $id) // Ignorar el local actual
                ->exists();

            if ($existeLocalEnMismaUbicacion) {
                return response()->json(['error' => 'Ya existe un local en esta ubicación.'], 422);
            }
        }

        // Establecer la fecha de última edición
        $validatedData['fecha_ultima_edicion'] = now();

        // Actualizar el local turístico con los datos validados
        $localTuristico->update($validatedData);

        // Retornar el local turístico actualizado
        return response()->json($localTuristico, 200);
    }


    // Eliminar (cambiar a inactivo) un local turístico
    public function destroy($id)
    {
        // Buscar el local turístico por ID
        $localTuristico = LocalTuristico::find($id);

        // Verificar si el local turístico existe
        if (!$localTuristico) {
            return response()->json(['message' => 'Local turístico no encontrado'], 404);
        }

        // Verificar si el local turístico ya está inactivo
        if ($localTuristico->estado === 'inactivo') {
            return response()->json(['message' => 'Este local turístico ya ha sido eliminado'], 409); // 409 Conflict
        }

        // Cambiar el estado a inactivo para "eliminar" el local
        $localTuristico->estado = 'inactivo';
        $localTuristico->save(); // Guardar los cambios en la base de datos

        return response()->json(['message' => 'Local turístico eliminado con éxito'], 200);
    }

    public function mostrarDataLocal($id)
    {
        // Obtener el local turístico
        $localTuristico = LocalTuristico::select('id', 'nombre', 'descripcion', 'direccion', 'latitud', 'longitud', 'estado', 'id_dueno')
            ->find($id);
        if (!$localTuristico) {
            return response()->json(['message' => 'Local Turistico no encontrado'], 404);
        }

        // Obtener al dueño del local
        $duenoLocal = DuenoLocal::where('id', $localTuristico->id_dueno)
            ->where('estado', 'activo')
            ->first();

        // Verificación si se encuentra o no al dueño
        $nombreDueno = $duenoLocal ? $duenoLocal->nombre . ' ' . $duenoLocal->apellido : "No encontrado";

        // Obtener los horarios de atención
        $horarioAtencion = HorarioAtencion::select('dia_semana', 'hora_inicio', 'hora_fin')
            ->where('id_local', $localTuristico->id)
            ->where('estado', 'activo')
            ->get();

        // Obtener los PrecioLocal
        $precioLocal = PrecioLocal::select('servicio', 'precio')
            ->where('id_local', $localTuristico->id)
            ->where('estado', 'activo')
            ->get();

        // Retornar una respuesta con la información más limpia
        return response()->json([
            'Local' => $localTuristico,
            'Propietario' => $nombreDueno,
            'Horarios' => $horarioAtencion,
            'Servicios' => $precioLocal
        ], 200);
    }


}



