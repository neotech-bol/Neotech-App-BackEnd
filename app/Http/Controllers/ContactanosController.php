<?php

namespace App\Http\Controllers;

use App\Models\Contactanos;
use Illuminate\Http\Request;

class ContactanosController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $item = Contactanos::where('nombre_completo', 'like', "%{$search}%")->orderBy('id', 'desc')->paginate(10);
        return response()->json(["mensaje" => "Datos cargados", "datos" => $item], 200);
    }
    public function store(Request $request)
    {
        $request->validate([
            'nombre_completo' => 'required|string|max:255',
            'correo' => 'required|email|max:255',
            'telefono' => 'required|string|max:20',
            'mensaje' => 'required|string',
            'departamento' => 'required|string|max:50',
        ]);
        // Guarda el mensaje en la base de datos
        $item =  new contactanos();
        $item->nombre_completo = $request->nombre_completo;
        $item->correo = $request->correo;
        $item->telefono = $request->telefono;
        $item->mensaje = $request->mensaje;
        $item->departamento = $request->departamento;
        $item->save();
        return response()->json(["mensaje" => "Envio Exitoso", "datos" => $item], 200);
    }
    public function destroy(string $id)
    {
        $contactanos = contactanos::find($id);
        if ($contactanos) {
            $contactanos->delete();
            return response()->json(["mensaje" => "Contacto eliminado con éxito"], 200);
        } else {
            return response()->json(["mensaje" => "Contacto no encontrado"], 404);
        }
    }
    public function countContactanos()
    {
        $total = contactanos::count();
        return response()->json(["mensaje" => "Total de contactanos", "total" => $total], 200);
    }
}
