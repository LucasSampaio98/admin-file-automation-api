<?php
// use Illuminate\Support\Facades\DB;
/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->options('/{any:.*}', function () {
    return response('', 200)
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
        ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
});

$router->get('/', function () use ($router) {
    phpinfo();
    return $router->app->version();
});

$router->post('/login', 'AuthController@login');
$router->get('/clientes', ['middleware' => 'auth', 'uses' => 'ClientController@index']);
$router->get('/cliente/{id}/pastas', ['middleware' => 'auth', 'uses' => 'FolderController@getFoldersByClient']);
$router->post('/cliente/{id}/pastas/{folder_id}/upload', ['middleware' => 'auth', 'uses' => 'FileController@uploadFiles']);
$router->get('/download/{file_id}', ['middleware' => 'auth', 'uses' => 'FileController@downloadFile']);
$router->get('/cliente/{id}/pastas/{folder_id}/arquivos', ['middleware' => 'auth', 'uses' => 'FileController@listFiles']);
$router->get('/cliente/{id}/pastas/{folder_id}/arquivos/download', ['middleware' => 'auth', 'uses' => 'FileController@downloadAllFiles']);
$router->get('/cliente/{id}/formatos', ['middleware' => 'auth', 'uses' => 'FormatController@listFormats']);
$router->post('/cliente/{client_id}/pastas', ['middleware' => 'auth', 'uses' => 'FolderController@createFolder']);


// $router->get('/test-db-connection', function () {
//     try {
//         // Tenta realizar uma consulta simples no banco de dados usando a Facade DB
//         $results = DB::select("SELECT 1");

//         // Se a consulta foi bem-sucedida, retorna uma mensagem de sucesso
//         return response()->json(['message' => 'Conexão com o banco de dados estabelecida com sucesso!']);
//     } catch (\Exception $e) {
//         // Se houve um erro, retorna o erro
//         return response()->json(['error' => $e->getMessage()], 500);
//     }
// });