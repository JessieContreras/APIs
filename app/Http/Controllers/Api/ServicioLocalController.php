<?php

namespace App\Http\Controllers\Api;

use App\Models\ServicioLocal;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ServicioLocalController extends Controller
{
    public function index()
    {
        return ServicioLocal::all();
    }

    public function show($id)
    {
        $servicioLocal = ServicioLocal::find($id);

        if (!$servicioLocal) {
            return response()->json(['message' => 'ServicioLocal no encontrado'], 404);
        }

        return response()->json($servicioLocal, 200);
    }

    public function indexActivos()
    {
        return response()->json(ServicioLocal::where('estado', 'activo')->get(), 200);
    }

    public function showActivo(string $id)
    {
        $servicioLocal = ServicioLocal::where('id', $id)->where('estado', 'activo')->first();
    
        if (!$servicioLocal) {
            return response()->json(['message' => 'ServicioLocal no encontrado o no está activo'], 404);
        }
    
        return response()->json($servicioLocal, 200);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'id_local' => 'required|integer',
            'servicio' => 'required|string|max:255',
            'precio' => 'required|numeric',
            'tipo' => 'required|string|max:255',
            'creado_por' => 'required|string|max:255',
        ]);

        $servicioExistente = ServicioLocal::where('id_local', $request->id_local)
                                          ->where('servicio', $request->servicio)
                                          ->where('estado', 'activo')
                                          ->first();

        if ($servicioExistente) {
            return response()->json(['message' => 'Ya existe un servicio activo con este nombre para este local.'], 409);
        }

        $validatedData['estado'] = 'activo';

        $servicioLocal = ServicioLocal::create($validatedData);

        return response()->json($servicioLocal, 201);
    }

    public function update(Request $request, string $id)
    {
        $servicioLocal = ServicioLocal::find($id);

        if (!$servicioLocal) {
            return response()->json(['message' => 'ServicioLocal no encontrado'], 404);
        }

        if ($servicioLocal->estado === 'inactivo') {
            return response()->json(['message' => 'El servicio está inactivo y no se puede actualizar.'], 409);
        }

        $validatedData = $request->validate([
            'id_local' => 'required|integer',
            'servicio' => 'required|string|max:255',
            'precio' => 'required|numeric',
            'tipo' => 'required|string|max:255',
            'editado_por' => 'required|string|max:255',
        ]);

        $servicioExistente = ServicioLocal::where('id_local', $request->id_local)
                                          ->where('servicio', $request->servicio)
                                          ->where('estado', 'activo')
                                          ->where('id', '!=', $id)
                                          ->first();

        if ($servicioExistente) {
            return response()->json(['message' => 'Ya existe otro servicio activo con este nombre para este local.'], 409);
        }

        $servicioLocal->update($validatedData);

        return response()->json($servicioLocal, 200);
    }

    public function destroy(string $id)
    {
        $servicioLocal = ServicioLocal::find($id);

        if (!$servicioLocal) {
            return response()->json(['message' => 'ServicioLocal no encontrado'], 404);
        }

        if ($servicioLocal->estado === 'inactivo') {
            return response()->json(['message' => 'Este servicio ya ha sido eliminado'], 409);
        }

        $servicioLocal->estado = 'inactivo';
        $servicioLocal->save();

        return response()->json(['message' => 'ServicioLocal eliminado con éxito'], 200);
    }
}
