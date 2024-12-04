<?php

namespace App\Http\Controllers\Api;

use App\Models\Asistente;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;


class AsistenteController extends Controller
{
    public function index()
    {
        $asistentes = Asistente::orderBy('id', 'desc')->get();
        return response()->json([
            'cantidad' => $asistentes->count(),
            'datos' => $asistentes
        ], 200);
    }

    // Mostrar un Asistente específico
    public function show($id)
    {
        $asistente = Asistente::find($id);

        if (!$asistente) {
            return response()->json(['message' => 'Asistente no encontrado'], 404);
        }

        return response()->json($asistente, 200);
    }

    // Mostrar todos los Administradores activos
    public function indexActivos()
    {
        $activos = Asistente::where('estado', 'activo')->orderBy('id', 'desc')->get();
        return response()->json([
            'cantidad' => $activos->count(),
            'datos' => $activos
        ], 200);
    }

    // Buscar un Asistente por ID solo si está activo
    public function showActivo(string $id)
    {
        $asistente = Asistente::where('id', $id)->where('estado', 'activo')->first();

        if (!$asistente) {
            return response()->json(['message' => 'Asistente no encontrado o no está activo'], 404);
        }

        return response()->json($asistente);
    }

    // Crear un nuevo Asistente esta en la parte de autenticacion
    public function registrarAsistente(Request $request)
    {
        // Verificar si los campos requeridos están presentes
        if (!$request->has(['nombre', 'apellido', 'cedula', 'telefono', 'email', 'contrasena', 'creado_por'])) {
            return response()->json(['message' => 'Faltan campos requeridos.'], 400);
        }

        // Validar el formato del email
        if (!filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['message' => 'Formato de email inválido.'], 400);
        }

        // Verificar si ya existe un asistente  con el mismo email o cédula
        $asistenteExistente = Asistente::where('cedula', $request->cedula)
            ->orWhere('email', $request->email)
            ->first();

        if ($asistenteExistente) {
            // Si existe, retornar un error 409 (conflict)
            return response()->json([
                'message' => 'El asistente ya existe con esta cédula o email.'
            ], 409);
        }

        try {
            // Crear el nuevo asistente en la base de datos
            $asistente = Asistente::create([
                'nombre' => $request->nombre,
                'apellido' => $request->apellido,
                'cedula' => $request->cedula,
                'telefono' => $request->telefono,
                'email' => $request->email,
                'contrasena' => Hash::make($request->contrasena), // Encriptar la contraseña
                'estado' => 'activo', // Agregar el estado por defecto
                'creado_por' => $request->creado_por,
                
            ]);

            // Crear el nuevo usuario en la base de datos
            $user = User::create([
                'name' => $request->nombre . ' ' . $request->apellido, // Concatenar nombre y apellido
                'email' => $request->email,
                'password' => Hash::make($request->contrasena), // Encriptar la contraseña
                'estado' => 'activo', // Agregar el estado por defecto
                'tipo' => 'asistente', // Para saber que el usuario es de tipo asistente
            ]);

        } catch (\Exception $e) {
            // Manejar cualquier excepción que ocurra al crear el asistente 
            return response()->json(['message' => 'Error al registrar el asistente.'], 500);
        }

        // Generar el token JWT
        $token = JWTAuth::fromUser($user);
        

        // Retornar una respuesta exitosa
        return response()->json([
            'message' => 'asistente registrado exitosamente',
            'Asistente' => $asistente,
            'user' => $user,
            'token' => $token
        ], 201);
    }


    // Actualizar un Asistente existente
    public function update(Request $request, string $id)
    {
        $asistente = Asistente::find($id);

        if (!$asistente) {
            return response()->json(['message' => 'Asistente no encontrado'], 404);
        }

        if ($asistente->estado !== 'activo') {
            return response()->json(['message' => 'Asistente no activo'], 403); // 403 Forbidden
        }

        // Buscar el usuario relacionado por el email del Asistente
        $user = User::where('email', $asistente->email)->first();

        if (!$user || $user->estado !== 'activo') {
            return response()->json(['message' => 'Asistente  no activo o no encontrado'], 403); // 403 Forbidden
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

        // Actualizar el Asistente sin la contraseña
        $asistente->update($dataToUpdate);


        if ($user) {
            // Actualizar el usuario con los nuevos datos del asistente
            $user->name = $request->input('nombre', $asistente->nombre) . ' ' . $request->input('apellido', $asistente->apellido);
            $user->email = $request->input('email', $asistente->email);

            // No actualizar la contraseña aquí, ya que se manejará en otra API
            $user->save(); // Guardar los cambios en el usuario
        }

        // Retornar una respuesta exitosa
        return response()->json([
            'message' => 'Asistente actualizado exitosamente',
            'Asistente' => $asistente
        ], 201);
    }

 
    public function destroy($id)
    {
        $asistente = Asistente::find($id);

        if (!$asistente) {
            return response()->json(['message' => 'Asistente no encontrado'], 404);
        }

        if ($asistente->estado !== 'activo') {
            return response()->json(['message' => 'Asistente ya eliminado'], 403); // 403 Forbidden
        }

        // Buscar el usuario relacionado por el email del administrador
        $user = User::where('email', $asistente->email)->first();
        if (!$user) {
            return response()->json(['message' => 'El Usuario del Asistente no encontrado'], 404); // 
        }
        if ($user->estado !== 'activo') {
            return response()->json(['message' => 'El Ususario del Asistente ya se encuentra Inactivo'], 403); 
        }

        if ($user) {
            // Cambiar el estado a inactivo
            $user->estado = 'inactivo';
            // No actualizar la contraseña aquí, ya que se manejará en otra API
            $user->save(); // Guardar los cambios en el usuario
        }

        // Cambiar el estado a inactivo
        $asistente->estado = 'inactivo'; 
        $asistente->save();

        return response()->json(['message' => 'Asistente eliminado con éxito'], 200);
    }

    public function activar(string $id)
    {
        $asistente = Asistente::find($id);

        if (!$asistente) {
            return response()->json(['message' => 'Asistente no encontrado'], 404);
        }

        if ($asistente->estado !== 'inactivo') {
            return response()->json(['message' => 'El Asistente ya se encuentra activo'], 403); // 403 Forbidden
        }

        // Buscar el usuario relacionado por el email del asistente
        $user = User::where('email', $asistente->email)->first();
        if (!$user) {
            return response()->json(['message' => 'El Usuario del Asistente no encontrado'], 404);
        }

        if ($user->estado !== 'inactivo') {
            return response()->json(['message' => 'El Usuario del Asistente ya se encuentra activo'], 403); // 403 Forbidden
        }

        // Cambiar el estado del asistente a activo
        $asistente->estado = 'activo'; 
        $asistente->save();

        // Cambiar el estado del usuario a activo
        $user->estado = 'activo';
        $user->save();

        return response()->json(['message' => 'Asistente activado con éxito']);
    }

}
