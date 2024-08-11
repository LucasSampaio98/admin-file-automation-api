<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Folder;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use ZipArchive;

class FileController extends Controller
{
    // Liberar extensão extension=fileinfo no php.ini
    public function downloadFile($file_id, Request $request)
    {
        try {
            // Encontra o arquivo pelo ID
            $file = File::findOrFail($file_id);

            // Verifica se o arquivo existe no sistema de arquivos
            if (!file_exists($file->file_path)) {
                return response()->json(['error' => 'File not found on server'], 404);
            }

            // Verifica se o usuário autenticado tem permissão para baixar o arquivo
            if ($request->auth->role !== 'admin' && $request->auth->id != $file->folder->client_id) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Faz o download do arquivo usando o helper response()->download()
            return response()->download($file->file_path, $file->file_name);

        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'File not found in database'], 404);
        }
    }

    public function listFiles($id, $folder_id, Request $request)
    {
        // Verifique se a pasta pertence ao cliente e se o usuário autenticado tem permissão
        $folder = Folder::where('id', $folder_id)->where('client_id', $id)->first();

        if (!$folder) {
            return response()->json(['error' => 'Folder not found'], 404);
        }

        if ($request->auth->role !== 'admin' && $request->auth->id != $folder->client_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Lista todos os arquivos na pasta específica
        $arquivos = File::where('folder_id', $folder_id)->get();

        return response()->json($arquivos);
    }

    public function uploadFiles(Request $request, $id, $folder_id)
    {
        // Verifique se o usuário autenticado é o cliente ou um admin
        if ($request->auth->role !== 'admin' && $request->auth->id != $id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Validação para garantir que arquivos foram enviados
        $this->validate($request, [
            'files' => 'required',
            'files.*' => 'file'
        ]);

        // Verifica se a pasta pertence ao cliente
        $folder = Folder::where('id', $folder_id)->where('client_id', $id)->first();
        if (!$folder) {
            return response()->json(['error' => 'Folder not found'], 404);
        }

        // Caminho absoluto onde os arquivos serão armazenados
        $baseDirectory = "C:/AdminFiles/uploads"; // Defina aqui o diretório no PC do admin

        // Diretório específico para os arquivos do cliente
        $directory = $baseDirectory . "/cliente-{$id}/{$folder->folder_name}/";

        // Cria o diretório se não existir
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        // Processa cada arquivo enviado
        $uploadedFiles = $request->file('files');
        if (!is_array($uploadedFiles)) {
            $uploadedFiles = [$uploadedFiles];
        }

        foreach ($uploadedFiles as $file) {
            $fileName = $file->getClientOriginalName();
            $filePath = $directory . $fileName;

            // Verifica se o arquivo já existe e renomeia se necessário
            $counter = 1;
            while (file_exists($filePath)) {
                $fileNameWithoutExt = pathinfo($fileName, PATHINFO_FILENAME);
                $extension = pathinfo($fileName, PATHINFO_EXTENSION);
                $fileName = $fileNameWithoutExt . " ({$counter})." . $extension;
                $filePath = $directory . $fileName;
                $counter++;
            }

            // Salva o arquivo no sistema de arquivos usando move_uploaded_file
            if (!move_uploaded_file($file->getPathname(), $filePath)) {
                return response()->json(['error' => 'Failed to store file'], 500);
            }

            // Salva as informações do arquivo no banco de dados
            $fileRecord = File::create([
                'folder_id' => $folder_id,
                'file_name' => $fileName,
                'file_path' => $filePath,
                'uploaded_by' => $request->auth->id
            ]);

            if (!$fileRecord) {
                return response()->json(['error' => 'Failed to save file record'], 500);
            }
        }

        return response()->json(['message' => 'Files uploaded successfully'], 200);
    }

    public function downloadAllFiles($id, $folder_id, Request $request)
    {
        // Verifique se a pasta pertence ao cliente e se o usuário autenticado tem permissão
        $folder = Folder::where('id', $folder_id)->where('client_id', $id)->first();

        if (!$folder) {
            return response()->json(['error' => 'Folder not found'], 404);
        }

        if ($request->auth->role !== 'admin' && $request->auth->id != $folder->client_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Obtenha todos os arquivos na pasta específica
        $files = File::where('folder_id', $folder_id)->get();

        if ($files->isEmpty()) {
            return response()->json(['error' => 'No files found in this folder'], 404);
        }

        // Caminho temporário para o arquivo ZIP
        $zipDirectory = storage_path('app/temp/');
        if (!is_dir($zipDirectory)) {
            mkdir($zipDirectory, 0755, true); // Cria o diretório se não existir
        }

        $zipFileName = 'arquivos_cliente_' . $id . '_pasta_' . $folder_id . '.zip';
        $zipFilePath = $zipDirectory . $zipFileName;

        // Cria um arquivo ZIP
        $zip = new ZipArchive;
        $zipOpenStatus = $zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        if ($zipOpenStatus !== TRUE) {
            return response()->json(['error' => 'Failed to create ZIP file', 'status' => $zipOpenStatus], 500);
        }

        foreach ($files as $file) {
            if (file_exists($file->file_path)) {
                $zip->addFile($file->file_path, basename($file->file_path));
            }
        }

        $zip->close();

        // Retorna o arquivo ZIP para download
        return response()->download($zipFilePath)->deleteFileAfterSend(true);
    }
}
