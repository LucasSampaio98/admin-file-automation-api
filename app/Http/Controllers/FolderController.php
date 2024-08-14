<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use App\Models\Client;
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
        // Valida que o campo 'formats' é um array e que não está vazio
        $this->validate($request, [
            'formats' => 'required|array|min:1',
            'formats.*' => 'string|max:255', // Valida cada formato dentro do array
        ]);

        // Verifica se o usuário autenticado é o cliente ou um admin
        $user = $request->auth;

        if ($user->role !== 'admin' && $user->id != $client_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Recupere o nome do cliente do banco de dados
        $client = Client::findOrFail($client_id);
        $clientName = $client->client_name; // Certifique-se de que 'name' é o campo correto

        $baseDirectory = "C:/AdminFiles/uploads/cliente-{$clientName}/";
        $createdFolders = [];

        foreach ($request->input('formats') as $format) {
            $folderName = "{$clientName}-{$format}";
            $directory = $baseDirectory . $folderName . '/';

            // Verifica se o diretório já existe e, se existir, incrementa o sufixo
            $originalDirectory = $directory;
            $counter = 1;
            while (is_dir($directory)) {
                $directory = $originalDirectory . " ({$counter})/";
                $folderName = "{$clientName}-{$format} ({$counter})";
                $counter++;
            }

            // Cria o diretório físico
            mkdir($directory, 0755, true);

            // Cria a nova pasta no banco de dados
            $folder = Folder::create([
                'client_id' => $client_id,
                'folder_name' => $folderName
            ]);

            $createdFolders[] = $folder->id;
        }

        return response()->json(['message' => 'Folders created successfully', 'folder_ids' => $createdFolders], 201);
    }


    private function createDirectoryAndFolder($client_id, $folderName, $baseDirectory)
    {
        // Inicializa o diretório onde a pasta será criada fisicamente
        $directory = $baseDirectory . $folderName . '/';
        $originalDirectory = $directory;
        $counter = 1;

        // Verifica se o diretório já existe e, se existir, incrementa o sufixo
        while (is_dir($directory)) {
            $directory = $originalDirectory . " ({$counter})/";
            $folderName = $folderName . " ({$counter})";
            $counter++;
        }

        // Cria o diretório físico
        mkdir($directory, 0755, true);

        // Cria a nova pasta no banco de dados e retorna o objeto Folder criado
        return Folder::create([
            'client_id' => $client_id,
            'folder_name' => $folderName
        ]);
    }
}
