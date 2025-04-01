<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use Illuminate\Http\Request;

class CitasController extends Controller
{
    public function index(){
        $items = Cita::orderBy('id', 'desc')->paginate(10);
        return response()->json(["message" => "Citas cargadas", "datos" => $items], 200);
    }
    public function store(Request $request){
        $request->validate([
            'nombre_completo' => 'required|string|max:255',
            'telefono' => 'required|string|max:20',
            'correo' => 'required|email|max:255',
            'fecha_de_cita' => 'required|date',
            'hora_de_cita' => 'required|date_format:H:i',
            'servicio_solicitado' => 'required|string|max:255',
            'departamento' => 'required|string|max:255',
        ]);

        $cita = new Cita();
        $cita->nombre_completo = $request->nombre_completo;
        $cita->telefono = $request->telefono;
        $cita->correo = $request->correo;
        $cita->fecha_de_cita = $request->fecha_de_cita;
        $cita->hora_de_cita = $request->hora_de_cita;
        $cita->servicio_solicitado = $request->servicio_solicitado;
        $cita->departamento = $request->departamento;
        $cita->mensaje = '';
        $cita->save();
        return response()->json(["message" => "Cita creada", "datos" => $cita], 201);
    }
    public function show($id){
        $cita = Cita::find($id);
        if (!$cita) {
            return response()->json(["message" => "Cita no encontrada"], 404);
        }
        return response()->json(["message" => "Cita cargada", "datos" => $cita], 200);
    }
    public function update(Request $request, $id){
        $cita = Cita::find($id);
        if (!$cita) {
            return response()->json(["message" => "Cita no encontrada"], 404);
        }

        $request->validate([
            'nombre_completo' => 'sometimes|required|string|max:255',
            'telefono' => 'sometimes|required|string|max:20',
            'correo' => 'sometimes|required|email|max:255',
            'fecha_de_cita' => 'sometimes|required|date',
            'hora_de_cita' => 'sometimes|required|date_format:H:i',
            'servicio_solicitado' => 'sometimes|required|string|max:255',
            'departamento' => 'sometimes|required|string|max:255',
        ]);

        $cita->nombre_completo = $request->nombre_completo;
        $cita->telefono = $request->telefono;
        $cita->correo = $request->correo;
        $cita->fecha_de_cita = $request->fecha_de_cita;
        $cita->hora_de_cita = $request->hora_de_cita;
        $cita->servicio_solicitado = $request->servicio_solicitado;
        $cita->departamento = $request->departamento;
        $cita->save();
        return response()->json(["message" => "Cita actualizada", "datos" => $cita], 200);
    }
    public function destroy($id){
        $cita = Cita::find($id);
        if (!$cita) {
            return response()->json(["message" => "Cita no encontrada"], 404);
        }
        $cita->delete();
        return response()->json(["message" => "Cita eliminada"], 200);
    }
    public function cambiarEstado ($id){
        $cita = Cita::find($id);
        if (!$cita) {
            return response()->json(["message" => "Cita no encontrada"], 404);
        }
        $cita->estado = !$cita->estado;
        $cita->save();
        return response()->json(["message" => "Estado de la cita actualizado", "datos" => $cita], 200);
    }
}
