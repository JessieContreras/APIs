<?php

namespace App\Http\Controllers\Api;

use App\Models\EtiquetaTuristica;
use App\Models\Imagen;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EtiquetaTuristicaController extends Controller
{
    // Listar todas las etiquetas turísticas
    public function index()
    {
        $etiquetaTuristica = EtiquetaTuristica::orderBy('id', 'desc')->get(); 
        return response()->json([
            'cantidad' => $etiquetaTuristica->count(),
            'datos' => $etiquetaTuristica
        ], 200);
    }

    // Mostrar una etiqueta turística específica
    public function show($id)
    {
        $etiquetaTuristica = EtiquetaTuristica::find($id);

        if (!$etiquetaTuristica) {
            return response()->json(['message' => 'Etiqueta turística no encontrada'], 404);
        }

        return response()->json($etiquetaTuristica, 200);
    }

    // Mostrar todos los EtiquetaTuristica activos
    public function indexActivos()
    {
        // Obtener las etiquetas activas
        $activos = EtiquetaTuristica::where('estado', 'activo')->orderBy('id', 'desc')->get();

        // Obtener las imágenes de tipo "etiquetas"
        $imagenes = Imagen::where('tipo', 'etiquetas')->get(['id_entidad', 'ruta_imagen']);

        // Crear un arreglo de imágenes asociadas a los ids de las etiquetas activas
        $imagenesAsociadas = $imagenes->pluck('ruta_imagen', 'id_entidad')->toArray();

        // Asociar la ruta de la imagen a cada etiqueta activa
        $activosConImagenes = $activos->map(function ($etiqueta) use ($imagenesAsociadas) {
            $etiqueta->ruta_imagen = isset($imagenesAsociadas[$etiqueta->id]) ? $imagenesAsociadas[$etiqueta->id] : null;
            return $etiqueta;
        });

        // Devolver los datos con las rutas de las imágenes
        return response()->json([
            'cantidad' => $activosConImagenes->count(),
            'datos' => $activosConImagenes
        ], 200);
    }


    // Buscar una etiquetaTuristica por ID solo si está activo
    public function showActivo(string $id)
    {
        $etiquetaTuristica = EtiquetaTuristica::where('id', $id)->where('estado', 'activo')->first();
    
        if (!$etiquetaTuristica) {
            return response()->json(['message' => 'Etiqueta turística no encontrada o no está activa'], 404);
        }
    
        return response()->json($etiquetaTuristica, 200);
    }
    


 
    // Crear una nueva etiqueta turística
    public function store(Request $request)
    {
        // Validar los datos
        $validatedData = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'creado_por' => 'required|string|max:255',
        ]);
        
        // Verificar si ya existe una etiqueta turística con el mismo nombre que esté activa
        $etiquetaTuristicaExistente = EtiquetaTuristica::where('nombre', $request->nombre)
            ->where('estado', 'activo') // Solo bloquear si está activa
            ->first(); 

        if ($etiquetaTuristicaExistente) {
            // Si ya existe una etiqueta con el mismo nombre y está activa
            return response()->json([
                'message' => 'La etiqueta turística ya existe y está activa.'
            ], 409);
        }

        // Agregar el estado 'activo' por defecto al array de datos
        $validatedData['estado'] = 'activo';

        // Crear la nueva etiqueta turística
        $etiquetaTuristica = EtiquetaTuristica::create($validatedData);

        // Retornar la respuesta de éxito
        return response()->json($etiquetaTuristica, 201);
    }


 
    // Actualizar una etiqueta turística existente
    public function update(Request $request, $id)
    {
        $etiquetaTuristica = EtiquetaTuristica::find($id);

        if (!$etiquetaTuristica) {
            return response()->json(['message' => 'Etiqueta turística no encontrada'], 404);
        }

        // Verificar si la etiqueta ya fue eliminado (estadp = 'inactivo')
        if ($etiquetaTuristica->estado === 'inactivo') {
            return response()->json([
                'message' => 'La etiqueta turística está inactiva y no se puede actualizar.'
            ], 409);
        }

        $validatedData = $request->validate([
            'nombre' => 'sometimes|string|max:255',
            'descripcion' => 'nullable|string',
            'editado_por' => 'required|string|max:255',
        ]);

        $etiquetaTuristica->update($validatedData);

        return response()->json($etiquetaTuristica, 200);
    }
 
    // Eliminar una etiqueta turística
    public function destroy($id)
    {
        $etiquetaTuristica = EtiquetaTuristica::find($id);

        if (!$etiquetaTuristica) {
            return response()->json(['message' => 'Etiqueta turística no encontrada'], 404);
        }

        // Verificar si ya está inactiva la etiqueta
        if ($etiquetaTuristica->estado === 'inactivo') {
            return response()->json(['message' => 'Esta etiqueta turistica ya ha sido eliminada'], 409); // 409 Conflict
        }

        // Cambiar el estado a eliminado
        $etiquetaTuristica->estado = 'inactivo'; 
        $etiquetaTuristica->save(); 

        return response()->json(['message' => 'Etiqueta Turistica eliminada con éxito'], 200);
    }

    public function activar(string $id)
    {
        $etiquetaTuristica = EtiquetaTuristica::find($id);

        if (!$etiquetaTuristica) {
            return response()->json(['message' => 'EtiquetaTuristica no encontrada'], 404);
        }

        if ($etiquetaTuristica->estado !== 'inactivo') {
            return response()->json(['message' => 'La EtiquetaTuristica ya se encuentra activo'], 403); // 403 Forbidden
        }

        // Cambiar el estado a activo
        $etiquetaTuristica->estado = 'activo'; 
        $etiquetaTuristica->save(); 

        return response()->json(['message' => 'EtiquetaTuristica activada con éxito']);
    }

    


}
