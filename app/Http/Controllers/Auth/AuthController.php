<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
//modelos
use App\Models\User;
use App\Models\Administrador;
use App\Models\Asistente;
//
use Illuminate\Support\Facades\Hash; // Para encriptar contraseñas
use Illuminate\Validation\ValidationException; // Para el manejo de excepciones
use Illuminate\Foundation\Validation\ValidatesRequests; // Importar el trait
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function registrarAdministrador(Request $request)
    {
        // Verificar si los campos requeridos están presentes
        if (!$request->has(['nombre', 'apellido', 'cedula', 'telefono', 'email', 'departamento', 'contrasena', 'creado_por'])) {
            return response()->json(['message' => 'Faltan campos requeridos.'], 400);
        }

        // Validar el formato del email
        if (!filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['message' => 'Formato de email inválido.'], 400);
        }

        // Verificar si ya existe un administrador con el mismo email o cédula
        $administradorExistente = Administrador::where('cedula', $request->cedula)
            ->orWhere('email', $request->email)
            ->first();

        if ($administradorExistente) {
            // Si existe, retornar un error
            return response()->json([
                'message' => 'El administrador ya existe con esta cédula o email.'
            ], 409);
        }

        try {
            // Crear el nuevo administrador en la base de datos
            $administrador = Administrador::create([
                'nombre' => $request->nombre,
                'apellido' => $request->apellido,
                'cedula' => $request->cedula,
                'telefono' => $request->telefono,
                'email' => $request->email,
                'departamento' => $request->departamento,
                'contrasena' => Hash::make($request->contrasena), // Encriptar la contraseña
                'creado_por' => $request->creado_por,
                'estado' => 'activo', // Agregar el estado por defecto
            ]);

            // Crear el nuevo usuario en la base de datos
            $user = User::create([
                'name' => $request->nombre . ' ' . $request->apellido, // Concatenar nombre y apellido
                'email' => $request->email,
                'password' => Hash::make($request->contrasena), // Encriptar la contraseña
                'estado' => 'activo', // Agregar el estado por defecto
                'tipo' => 'Admin', // Para reconocer que el usuario es admin
            ]);

        } catch (\Exception $e) {
            
            // Manejar cualquier excepción que ocurra al crear el administrador
            return response()->json(['message' => 'Error al registrar el administrador.'], 500);
        }

        // Generar el token JWT
        $token = JWTAuth::fromUser($user);
       

        // Retornar una respuesta exitosa
        return response()->json([
            'message' => 'Usuario registrado exitosamente',
            'Administrador' => $administrador,
            'user' => $user,
            'token' => $token
        ], 201);
    }

    

    public function login(Request $request)
    {
        // Verificar si los campos requeridos están presentes
        if (!$request->has(['email', 'password'])) {
            return response()->json(['message' => 'Faltan campos requeridos.'], 400);
        }
    
        $credenciales = $request->only('email', 'password');
        
        try {
            if (!$token = JWTAuth::attempt($credenciales)) {
                return response()->json([
                    'error' => 'Credenciales Invalidas'
                ], 400);
            }
    
            // Obtener el usuario autenticado
            $user = auth()->user();
    
            // Validar que el usuario esté activo
            if ($user->estado !== 'activo') {
                return response()->json([
                    'error' => 'El usuario no está activo.'
                ], 403);
            }
        } catch (JWTException $e) {
            return response()->json([
                'error' => 'Token no creado'
            ], 500);
        }
    
        // Retornar el token y el tipo de usuario
        return response()->json([
            'token' => $token,
            'tipo' => $user->tipo // Solo enviamos el tipo de usuario
        ]);
    }
    

    public function activarAdmin(string $id)
    {
        $administrador = Administrador::find($id);

        if (!$administrador) {
            return response()->json(['message' => 'Administrador no encontrado'], 404);
        }

        if ($administrador->estado !== 'inactivo') {
            return response()->json(['message' => 'El Administrador ya se encuentra activo'], 403); // 403 Forbidden
        }

        // Cambiar el estado a activo
        $administrador->estado = 'activo'; 
        $administrador->save(); 

        return response()->json(['message' => 'Administrador activado con éxito']);
    }


}
