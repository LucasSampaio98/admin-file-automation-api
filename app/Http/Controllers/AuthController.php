<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // Valida as entradas
        $this->validate($request, [
            'username' => 'required|string',
            'password' => 'required|string'
        ]);

        // Busca o usuário pelo nome de usuário
        $user = User::where('username', $request->input('username'))->first();

        // Verifica se o usuário existe e se a senha está correta
        if ($user && password_verify($request->input('password'), $user->password)) {
            // Gera o token JWT
            $token = JWT::encode([
                'id' => $user->id,
                'role' => $user->role,
                'exp' => time() + 60 * 60  // 1 hora de validade
            ], env('JWT_SECRET'), 'HS256');

            // Retorna o token
            return response()->json(['token' => $token]);
        } else {
            // Credenciais inválidas
            return response()->json(['error' => 'Invalid credentials'], 401);
        }
    }
}