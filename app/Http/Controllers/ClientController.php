<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{

    public function index(Request $request)
    {
        // Verifique se o objeto $request->auth existe e se a propriedade 'role' está definida
        if (!isset($request->auth) || !isset($request->auth->role)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Verifica se o usuário autenticado é um admin
        if ($request->auth->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Retorna a lista de todos os clientes
        $clientes = Client::all();
        return response()->json($clientes);
    }
}
