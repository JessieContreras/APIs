<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Administrador;
use App\Models\User;

class AdministradorController extends Controller
{
    // Mostrar todos los Administradores (activos e inactivos)
    public function index()
    {
        $administrador = Administrador::all(); 
        return response()->json([
            'cantidad' => $administrador->count(),
            'datos' => $administrador
        ], 200);
    }

    // Buscar un Administrador por ID sin importar el estado
    public function show($id)
    {
        $administrador = Administrador::find($id);

        if (!$administrador) {
            return response()->json(['message' => 'Administrador no encontrado'], 404);
        }

        return response()->json($administrador, 200);
    }

    // Mostrar todos los Administradores activos
    public function indexActivos()
    {
        $activos = Administrador::where('estado', 'activo')->get();
        return response()->json([
            'cantidad' => $activos->count(),
            'datos' => $activos
        ], 200);
    }

    // Buscar un Administrador por ID solo si está activo
    public function showActivo(string $id)
    {
        $administrador = Administrador::where('id', $id)->where('estado', 'activo')->first();

        if (!$administrador) {
            return response()->json(['message' => 'Administrador no encontrado o no está activo'], 404);
        }

        return response()->json($administrador);
    }




    //La funcion para reguistrar administradores esta en el apartado de autenticacion 
   
    


    //Actualizar Administrador
    public function update(Request $request, string $id)
    {
        $administrador = Administrador::find($id);

        if (!$administrador) {
            return response()->json(['message' => 'Administrador no encontrado'], 404);
        }

        if ($administrador->estado !== 'activo') {
            return response()->json(['message' => 'administrador no activo'], 403); // 403 Forbidden
        }

        // Buscar el usuario relacionado por el email del administrador
        $user = User::where('email', $administrador->email)->first();

        if (!$user || $user->estado !== 'activo') {
            return response()->json(['message' => 'administrador no activo o no encontrado'], 403); // 403 Forbidden
        }

        $request->validate([
            'nombre' => 'sometimes|required|string|max:255',
            'apellido' => 'sometimes|required|string|max:255',
            'cedula' => 'sometimes|required|string|max:20',
            'telefono' => 'sometimes|required|string|max:20',
            'email' => 'sometimes|required|string|email|max:255|unique:administradores,email,' . $id,
            'departamento' => 'sometimes|required|string|max:255',
            'editado_por' => 'sometimes|required|string|max:255',
        ]);

        // Excluir el campo 'contrasena' en la actualización
        $dataToUpdate = $request->except(['contrasena']);

        // Actualizar el administrador sin la contraseña
        $administrador->update($dataToUpdate);

        if ($user) {
            // Actualizar el usuario con los nuevos datos del administrador
            $user->name = $request->input('nombre', $administrador->nombre) . ' ' . $request->input('apellido', $administrador->apellido);
            $user->email = $request->input('email', $administrador->email);

            // No actualizar la contraseña aquí, ya que se manejará en otra API
            $user->save(); // Guardar los cambios en el usuario
        }

        // Retornar una respuesta exitosa
        return response()->json([
            'message' => 'Administrador actualizado exitosamente',
            'Administrador' => $administrador
        ], 201);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $administrador = Administrador::find($id);

        if (!$administrador) {
            return response()->json(['message' => 'Administrador no encontrado'], 404);
        }

        if ($administrador->estado !== 'activo') {
            return response()->json(['message' => 'El Administrador ya se encuentra Inactivo'], 403); // 403 Forbidden
        }

        // Buscar el usuario relacionado por el email del administrador
        $user = User::where('email', $administrador->email)->first();
        if (!$user) {
            return response()->json(['message' => 'El Usuario del Administrador no encontrado'], 404); // 
        }
        if ($user->estado !== 'activo') {
            return response()->json(['message' => 'El Administrador ya se encuentra Inactivo'], 403); 
        }

        if ($user) {
            // Cambiar el estado a inactivo
            $user->estado = 'inactivo';
            // No actualizar la contraseña aquí, ya que se manejará en otra API
            $user->save(); // Guardar los cambios en el usuario
        }


        // Cambiar el estado a inactivo
        $administrador->estado = 'inactivo'; 
        // No actualizar la contraseña aquí, ya que se manejará en otra API
        $administrador->save(); 

        return response()->json(['message' => 'Administrador eliminado con éxito']);
    }

    public function activar(string $id)
    {
        $administrador = Administrador::find($id);

        if (!$administrador) {
            return response()->json(['message' => 'Administrador no encontrado'], 404);
        }

        if ($administrador->estado !== 'inactivo') {
            return response()->json(['message' => 'El Administrador ya se encuentra activo'], 403); // 403 Forbidden
        }

        // Buscar el usuario relacionado por el email del administrador
        $user = User::where('email', $administrador->email)->first();
        if (!$user) {
            return response()->json(['message' => 'El Usuario del Administrador no encontrado'], 404);
        }

        if ($user->estado !== 'inactivo') {
            return response()->json(['message' => 'El Usuario del Administrador ya se encuentra activo'], 403); 
        }

        // Cambiar el estado del administrador a activo
        $administrador->estado = 'activo'; 
        $administrador->save();

        // Cambiar el estado del usuario a activo
        $user->estado = 'activo';
        $user->save();

        return response()->json(['message' => 'Administrador activado con éxito']);
    }

}
