<?php

namespace App\Http\Controllers;

use App\Models\testimony;
use Illuminate\Http\Request;

class TestimoniosController extends Controller
{
    public function index(Request $request) {
        // Obtener los parámetros de búsqueda y filtro
        $search = $request->input('search'); // Parámetro de búsqueda
        $estado = $request->input('estado'); // Parámetro de filtro por estado
    
        // Construir la consulta base
        $query = Testimony::query();
    
        // Aplicar filtro por estado si se proporciona
        if ($estado) {
            $query->where('estado', $estado);
        }
    
        // Aplicar búsqueda si se proporciona
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nombre_completo', 'like', "%{$search}%")
                  ->orWhere('ocupacion', 'like', "%{$search}%")
                  ->orWhere('experiencia', 'like', "%{$search}%");
            });
        }
    
        // Obtener los resultados paginados
        $items = $query->orderBy('id', 'desc')->paginate(10);
    
        return response()->json(["mensaje" => "Testimonios cargados", "datos" => $items], 200);
    }
    public function show(String $id){
        $item = testimony::find($id);
        if (!$item) {
            return response()->json(["mensaje" => "Testimonio no encontrado"], 404);
        }
        return response()->json(["mensaje" => "Testimonio cargado", "datos" => $item], 200);
    }
    public function store(Request $request){
        $request->validate([
            'nombre_completo' =>'required|string|max:255',
            'ocupacion' =>'required|string|max:255',
            'experiencia' =>'required|string',
            'calificacion' =>'required|integer|between:1,5',
        ]);
        $testimonio = new testimony();
        $testimonio->nombre_completo = $request->nombre_completo;
        $testimonio->ocupacion = $request->ocupacion;
        $testimonio->experiencia = $request->experiencia;
        $testimonio->calificacion = $request->calificacion;
        $testimonio->save();
        return response()->json(["mensaje" => "Testimonio creado correctamente"], 200);
    }
    public function update(Request $request, $id) {
        // Validar los datos de entrada
        $request->validate([
            'nombre_completo' => 'sometimes|required|string|max:255',
            'ocupacion' => 'sometimes|required|string|max:255',
            'experiencia' => 'sometimes|required|string',
            'calificacion' => 'sometimes|required|integer|between:1,5',
            'estado' => 'sometimes|in:pendiente,aprobado,rechazado', // Si deseas permitir la actualización del estado
        ]);
    
        // Buscar el testimonio por ID
        $testimonio = Testimony::find($id);
    
        // Verificar si el testimonio existe
        if (!$testimonio) {
            return response()->json(["mensaje" => "Testimonio no encontrado"], 404);
        }
    
        // Actualizar los campos del testimonio
        if ($request->has('nombre_completo')) {
            $testimonio->nombre_completo = $request->nombre_completo;
        }
        if ($request->has('ocupacion')) {
            $testimonio->ocupacion = $request->ocupacion;
        }
        if ($request->has('experiencia')) {
            $testimonio->experiencia = $request->experiencia;
        }
        if ($request->has('calificacion')) {
            $testimonio->calificacion = $request->calificacion;
        }
        if ($request->has('estado')) {
            $testimonio->estado = $request->estado;
        }
    
        // Guardar los cambios
        $testimonio->save();
    
        return response()->json(["mensaje" => "Testimonio actualizado correctamente", "datos" => $testimonio], 200);
    }
    public function cambiarEstado(Request $request, $id) {
        // Validar el estado recibido
        $request->validate([
            'estado' => 'required|in:pendiente,aprobado,rechazado', // Asegúrate de que el estado sea uno de los permitidos
        ]);
    
        // Buscar el testimonio por ID
        $testimonio = Testimony::find($id);
    
        // Verificar si el testimonio existe
        if (!$testimonio) {
            return response()->json(["mensaje" => "Testimonio no encontrado"], 404);
        }
    
        // Cambiar el estado
        $testimonio->estado = $request->estado;
        $testimonio->save();
    
        return response()->json(["mensaje" => "Estado del testimonio actualizado correctamente", "datos" => $testimonio], 200);
    }
    public function indexActivos() {
        // Obtener solo los testimonios aprobados
        $items = Testimony::where('estado', 'aprobado')->orderBy('id', 'desc')->get();
        
        return response()->json(["mensaje" => "Testimonios aprobados cargados", "datos" => $items], 200);
    }
}
