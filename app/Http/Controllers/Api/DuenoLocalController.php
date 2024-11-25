<?php

namespace App\Http\Controllers\Api;

use App\Models\DuenoLocal;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DuenoLocalController extends Controller
{
    // Listar todos los dueños locales
    public function index()
    {
        $duenoLocal = DuenoLocal::all();
        return response()->json([
            'cantidad' => $duenoLocal->count(),
            'datos' => $duenoLocal
        ], 200);
    }

    // Mostrar un dueño local específico
    public function show($id)
    {
        $duenoLocal = DuenoLocal::find($id);

        if (!$duenoLocal) {
            return response()->json(['message' => 'Dueño local no encontrado'], 404);
        }

        return response()->json($duenoLocal, 200);
    }

    // Mostrar todos los Administradores activos
    public function indexActivos()
    {
        $activos = DuenoLocal::where('estado', 'activo')->get();
        return response()->json([
            'cantidad' => $activos->count(),
            'datos' => $activos
        ], 200);
    }

    // Buscar un Dueño Local por ID solo si está activo
    public function showActivo(string $id)
    {
        $duenoLocal = DuenoLocal::where('id', $id)->where('estado', 'activo')->first();

        if (!$duenoLocal) {
            return response()->json(['message' => 'DuenoLocal no encontrado o no está activo'], 404);
        }

        return response()->json($duenoLocal);
    }


    // Crear un nuevo DuenoLocal esta en la parte de autenticacion
    public function store(Request $request)
    {
        // Verificar si los campos requeridos están presentes
        if (!$request->has(['nombre', 'apellido', 'cedula', 'telefono', 'email', 'contrasena', 'creado_por'])) {
            return response()->json(['message' => 'Faltan campos requeridos.'], 400);
        }

        // Validar el formato del email
        if (!filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['message' => 'Formato de email inválido.'], 400);
        }

        // Verificar si ya existe un DuenoLocal  con el mismo email o cédula
        $duenoLocalExistente = DuenoLocal::where('cedula', $request->cedula)
            ->orWhere('email', $request->email)
            ->first();

        if ($duenoLocalExistente) {
            // Si existe, retornar un error 409 (conflict)
            return response()->json([
                'message' => 'El duenoLocal ya existe con esta cédula o email.'
            ], 409);
        }

        try {
            // Crear el nuevo duenoLocal en la base de datos
            $duenoLocal = DuenoLocal::create([
                'nombre' => $request->nombre,
                'apellido' => $request->apellido,
                'cedula' => $request->cedula,
                'telefono' => $request->telefono,
                'email' => $request->email,
                'contrasena' => Hash::make($request->contrasena), // Encriptar la contraseña
                'estado' => 'activo', // Agregar el estado por defecto
                'creado_por' => $request->creado_por,
                
            ]);

        } catch (\Exception $e) {
            // Manejar cualquier excepción que ocurra al crear el duenoLocal 
            return response()->json(['message' => 'Error al registrar el duenoLocal.'], 500);
        }
        

        // Retornar una respuesta exitosa
        return response()->json([
            'message' => 'duenoLocal registrado exitosamente',
            'DuenoLocal' => $duenoLocal
        ], 201);
    }

    // Actualizar un dueño local existente
    public function update(Request $request, string $id)
    {
        $duenoLocal = DuenoLocal::find($id);

        if (!$duenoLocal) {
            return response()->json(['message' => 'Dueño local no encontrado'], 404);
        }

        if ($duenoLocal->estado !== 'activo') {
            return response()->json(['message' => 'dueño Local no activo'], 403); // 403 Forbidden
        }


        $request->validate([
            'nombre' => 'sometimes|string|max:255',
            'apellido' => 'sometimes|string|max:255',
            'cedula' => 'sometimes|string|max:10',
            'telefono' => 'sometimes|string|max:15',
            'email' => 'sometimes|string|email|max:255',
            'editado_por' => 'required|string|max:255',
        ]);

        // Excluir el campo 'contrasena' en la actualización
        $dataToUpdate = $request->except(['contrasena']);

        // Actualizar el dueño local sin la contraseña
        $duenoLocal->update($dataToUpdate);


        // Retornar una respuesta exitosa
        return response()->json([
            'message' => 'Dueño del local actualizado exitosamente',
            'Dueño Local' => $duenoLocal
        ], 201);
    }

    // Eliminar un dueño local
    public function destroy($id)
    {
        $duenoLocal = DuenoLocal::find($id);

        if (!$duenoLocal) {
            return response()->json(['message' => 'Dueño local no encontrado'], 404);
        }

        if ($duenoLocal->estado !== 'activo') {
            return response()->json(['message' => 'Dueño Local ya eliminado'], 403); // 403 Forbidden
        }

        // Cambiar el estado a inactivo
        $duenoLocal->estado = 'inactivo'; 
        $duenoLocal->save();

        return response()->json(['message' => 'Dueño local eliminado con éxito'], 200);
    }

    public function activar(string $id)
    {
        $duenoLocal = DuenoLocal::find($id);

        if (!$duenoLocal) {
            return response()->json(['message' => 'DuenoLocal no encontrado'], 404);
        }

        if ($duenoLocal->estado !== 'inactivo') {
            return response()->json(['message' => 'El DuenoLocal ya se encuentra activo'], 403); // 403 Forbidden
        }

        // Cambiar el estado a activo
        $duenoLocal->estado = 'activo'; 
        $duenoLocal->save(); 

        return response()->json(['message' => 'DuenoLocal activado con éxito']);
    }
}
