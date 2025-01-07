<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $item = User::with('roles')->paginate(10);
        return response()->json(["mensaje" => "Usuarios cargados correctamente", "datos" => $item], 200);
    }
}
