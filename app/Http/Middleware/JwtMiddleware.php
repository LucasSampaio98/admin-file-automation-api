<?php

namespace App\Http\Middleware;

use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;

class JwtMiddleware
{
    public function handle($request, Closure $next)
    {
        $token = $request->header('Authorization');

        if (!$token) {
            return response()->json(['error' => 'Token not provided'], 401);
        }

        try {
            // Remove "Bearer " do início do token
            $token = str_replace('Bearer ', '', $token);

            // Armazena o JWT_SECRET em uma variável antes de usá-lo
            $secretKey = env('JWT_SECRET');

            $credentials = JWT::decode($token, new \Firebase\JWT\Key($secretKey, 'HS256'));
        } catch (ExpiredException $e) {
            return response()->json(['error' => 'Token expired'], 401);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error decoding token'], 401);
        }

        // Anexa as credenciais do token na requisição
        $request->auth = $credentials;

        return $next($request);
    }
}
