<?php

namespace App\Http\Controllers\Api;

use App\Models\HorarioAtencion;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HorarioAtencionController extends Controller
{
    // Listar todos los horarios de atención
    public function index()
    {
        $horarioAtencion = HorarioAtencion::all();
        return response()->json([
            'cantidad' => $horarioAtencion->count(),
            'datos' => $horarioAtencion
        ], 200);
    }

    // Mostrar un horario de atención específico
    public function show($id)
    {
        $horarioAtencion = HorarioAtencion::where('id_local', $id)->get();

        if (!$horarioAtencion) {
            return response()->json(['message' => 'Horario de atención no encontrado'], 404);
        }

        return response()->json($horarioAtencion, 200);
    }

    // Mostrar todos los HorarioAtencion activos
    public function indexActivos()
    {
        $activos = HorarioAtencion::where('estado', 'activo')->get();
        return response()->json([
            'cantidad' => $activos->count(),
            'datos' => $activos
        ], 200);
    }

    // Buscar una horarioAtencion por ID solo si está activo
    public function showActivo(string $id)
    {
        $horarioAtencion = HorarioAtencion::where('id_local', $id)->where('estado', 'activo')->get();
    
        if (!$horarioAtencion) {
            return response()->json(['message' => 'Horario de atención no encontrada o no está activa'], 404);
        }
    
        return response()->json($horarioAtencion, 200);
    }
    


    // Crear un nuevo horario de atención
    // por ejemplo id local 1 dia lunes hora inicio 08:00 hora fin 16:00 creado por Ricardo Pilozo
    public function store(Request $request)
    {
        // Validar los datos de entrada
        $validatedData = $request->validate([
            'id_local' => 'required|integer',
            'dia_semana' => 'required|string|max:10',
            'hora_inicio' => 'required|date_format:H:i', // Validación para formato de tiempo
            'hora_fin' => 'required|date_format:H:i',    // Validación para formato de tiempo
            'creado_por' => 'required|string|max:255',
        ]);

        // Agregar estado por defecto si no viene en la petición
        $validatedData['estado'] = 'activo';

        // Verificar si existe algún horario activo que se solape con el nuevo horario
        $existeHorarioSolapado = HorarioAtencion::where('id_local', $validatedData['id_local'])
            ->where('dia_semana', $validatedData['dia_semana'])
            ->where('estado', 'activo') // Solo se consideran horarios activos
            ->where(function($query) use ($validatedData) {
                $query->where(function($q) use ($validatedData) {
                    $q->where('hora_inicio', '<', $validatedData['hora_fin'])
                    ->where('hora_fin', '>', $validatedData['hora_inicio']);
                });
            })
            ->exists();

        if ($existeHorarioSolapado) {
            return response()->json([
                'error' => 'Ya existe un horario activo que se solapa con este intervalo de tiempo.'
            ], 400);
        }

        // Crear el nuevo horario
        $horarioAtencion = HorarioAtencion::create([
            'id_local' => $validatedData['id_local'],
            'dia_semana' => $validatedData['dia_semana'],
            'hora_inicio' => $validatedData['hora_inicio'],
            'hora_fin' => $validatedData['hora_fin'],
            'estado' => $validatedData['estado'],
            'creado_por' => $validatedData['creado_por'],
        ]);

        return response()->json($horarioAtencion, 201);
    }



    // Actualizar un horario de atención existente
    public function update(Request $request, $id)
    {
        // Buscar el horario por ID
        $horarioAtencion = HorarioAtencion::find($id);

        if (!$horarioAtencion) {
            return response()->json(['message' => 'Horario de atención no encontrado'], 404);
        }

        // Validar los datos de entrada
        $validatedData = $request->validate([
            'id_local' => 'sometimes|integer',
            'dia_semana' => 'sometimes|string|max:10',
            'hora_inicio' => 'sometimes|date_format:H:i', // Validación para formato de tiempo
            'hora_fin' => 'sometimes|date_format:H:i',    // Validación para formato de tiempo
            'editado_por' => 'required|string|max:255',
        ]);

        // Tomar los valores actuales o los nuevos del request
        $id_local = $validatedData['id_local'] ?? $horarioAtencion->id_local;
        $dia_semana = $validatedData['dia_semana'] ?? $horarioAtencion->dia_semana;
        $hora_inicio = $validatedData['hora_inicio'] ?? $horarioAtencion->hora_inicio;
        $hora_fin = $validatedData['hora_fin'] ?? $horarioAtencion->hora_fin;

        // Verificar si existe algún horario activo que se solape con el nuevo horario (excepto el actual)
        $existeHorarioSolapado = HorarioAtencion::where('id_local', $id_local)
            ->where('dia_semana', $dia_semana)
            ->where('estado', 'activo') // Solo se consideran horarios activos
            ->where('id', '!=', $id) // Excluir el horario que se está actualizando
            ->where(function($query) use ($hora_inicio, $hora_fin) {
                $query->where(function($q) use ($hora_inicio, $hora_fin) {
                    $q->where('hora_inicio', '<', $hora_fin)
                    ->where('hora_fin', '>', $hora_inicio);
                });
            })
            ->exists();

        if ($existeHorarioSolapado) {
            return response()->json([
                'error' => 'Ya existe un horario activo que se solapa con este intervalo de tiempo.'
            ], 400);
        }

        // Actualizar el horario de atención con los valores nuevos o existentes
        $horarioAtencion->update([
            'id_local' => $id_local,
            'dia_semana' => $dia_semana,
            'hora_inicio' => $hora_inicio,
            'hora_fin' => $hora_fin,
            'editado_por' => $validatedData['editado_por'],
            'fecha_ultima_edicion' => now(),  // Actualizar la fecha de última edición
        ]);

        return response()->json($horarioAtencion, 200);
    }



    // Eliminar un horario de atención
    public function destroy($id)
    {
        $horarioAtencion = HorarioAtencion::find($id);

        if (!$horarioAtencion) {
            return response()->json(['message' => 'Horario de atención no encontrado'], 404);
        }

        // Verificar si ya está inactiva El horario de atención
        if ($horarioAtencion->estado === 'inactivo') {
            return response()->json(['message' => 'El horario de atención ya ha sido eliminado'], 409); // 409 Conflict
        }

        // Cambiar el estado a eliminado
        $horarioAtencion->estado = 'inactivo'; 
        $horarioAtencion->save(); 

        return response()->json(['message' => 'El horario de atención eliminado con éxito'], 200);

    }
}
