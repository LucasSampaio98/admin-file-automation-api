<?php

namespace App\Http\Controllers;

use App\Models\Format;
use Illuminate\Http\Request;

class FormatController extends Controller
{
    public function listFormats($id, Request $request)
    {
        $user = $request->auth;

        if ($user->role === 'admin') {
            // O admin pode ver todos os formatos
            $formatos = Format::all()->map(function ($format) {
                return [
                    'format_name' => $format->format_name,
                    'enabled' => true // Todos os formatos são habilitados para o admin
                ];
            });
        } else {
            // O cliente vê apenas os formatos disponíveis para ele
            $formatos = Format::leftJoin('client_formats', function ($join) use ($id) {
                $join->on('formats.id', '=', 'client_formats.format_id')
                    ->where('client_formats.client_id', '=', $id);
            })
                ->select('formats.format_name', 'client_formats.client_id')
                ->get()
                ->map(function ($format) {
                    return [
                        'format_name' => $format->format_name,
                        'enabled' => $format->client_id !== null // Habilitado se estiver na tabela client_formats
                    ];
                });
        }

        return response()->json($formatos);
    }
}
