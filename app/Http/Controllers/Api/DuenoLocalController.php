<?php

namespace App\Http\Controllers\Api;

use App\Models\DuenoLocal;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DuenoLocalController extends Controller
{
    // Listar todos los dueños locales
    public function index()
    {
        return DuenoLocal::all();
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
        return DuenoLocal::where('estado', 'activo')->get(); // Filtra solo los dueños de los locales activos
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




    // Crear un nuevo dueño local esta en la parte de autenticacion
   



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

        // Buscar el usuario relacionado por el email del dueño del local
        $user = User::where('email', $duenoLocal->email)->first();

        if (!$user || $user->estado !== 'activo') {
            return response()->json(['message' => 'dueño Local  no activo o no encontrado'], 403); // 403 Forbidden
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


        if ($user) {
            // Actualizar el usuario con los nuevos datos del duenoLocal
            $user->name = $request->input('nombre', $duenoLocal->nombre) . ' ' . $request->input('apellido', $duenoLocal->apellido);
            $user->email = $request->input('email', $duenoLocal->email);

            // No actualizar la contraseña aquí, ya que se manejará en otra API
            $user->save(); // Guardar los cambios en el usuario
        }

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
            return response()->json(['message' => 'Dueño Local no encontrado'], 403); // 403 Forbidden
        }

        // Cambiar el estado a inactivo
        $duenoLocal->estado = 'inactivo'; 
        $duenoLocal->save();

        return response()->json(['message' => 'Dueño local eliminado con éxito'], 200);
    }
}
