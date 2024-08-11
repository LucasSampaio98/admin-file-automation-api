<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use Illuminate\Http\Request;

class FolderController extends Controller
{
    public function getFoldersByClient($id, Request $request)
    {
        // Verifique se o objeto $request->auth existe e se a propriedade 'role' está definida
        if (!isset($request->auth) || !isset($request->auth->role)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Verifica se o usuário autenticado é o cliente ou um admin
        if ($request->auth->role !== 'admin' && $request->auth->id != $id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Retorna a lista de todas as pastas associadas ao cliente especificado
        $pastas = Folder::where('client_id', $id)->get();
        return response()->json($pastas);
    }

    public function createFolder(Request $request, $client_id)
    {
        // Valida os dados da requisição
        $this->validate($request, [
            'folder_name' => 'required|string|max:255',
        ]);

        // Verifica se o usuário autenticado é o cliente ou um admin
        $user = $request->auth;

        if ($user->role !== 'admin' && $user->id != $client_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Recebe o nome da pasta e define o diretório base
        $folderName = $request->input('folder_name');
        $baseDirectory = "C:/AdminFiles/uploads/cliente-{$client_id}/";

        // Inicializa o diretório onde a pasta será criada fisicamente
        $directory = $baseDirectory . $folderName . '/';
        $originalDirectory = $directory;
        $counter = 1;

        // Verifica se o diretório já existe e, se existir, incrementa o sufixo
        while (is_dir($directory)) {
            $directory = $originalDirectory . " ({$counter})/";
            $folderName = $request->input('folder_name') . " ({$counter})";
            $counter++;
        }

        // Cria o diretório físico
        mkdir($directory, 0755, true);

        // Cria a nova pasta no banco de dados
        $folder = Folder::create([
            'client_id' => $client_id,
            'folder_name' => $folderName
        ]);

        return response()->json(['message' => 'Folder created successfully', 'folder' => $folder], 201);
    }
}
