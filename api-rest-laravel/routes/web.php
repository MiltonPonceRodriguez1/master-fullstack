<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Incluir clases
use App\Http\Middleware\ApiAuthMiddleware;

Route::get('/', function () {
    return view('welcome');
});

/* RUTAS DE PRUEBA */
/*Route::get('/pruebas/{nombre?}', function ($nombre = null) {
    $texto = '<h2> Texto desde una ruta </h2>';
    $texto .= 'Nombre: '.$nombre;
    return $texto;
});*/

Route::get('/pruebas/{nombre?}', function ($nombre = null) {
    $texto = '<h2> Texto desde una ruta </h2>';
    $texto .= 'Nombre: '.$nombre;
    return view('pruebas', array(
        'texto' => $texto
    ));
});

Route::get('/animales', 'PruebasController@index');
Route::get('/test-orm', 'PruebasController@testOrm');

/* RUTAS DEL API */

    /* METODOS HTTP comunes (PETICIONES)
    * GET: Conseguir datos o recursos (Pasar a otra URL)
    * POST: Guardar datos, recursos o hacer logica
    * PUT: Actualizar datos o recursos.
    * DELETE: Eliminar datos o recursos. 
    */

    //RUTAS DE PRUEBA
    //Route::get('/usuario/pruebas', 'UserController@pruebas');
    //Route::get('/categoria/pruebas', 'CategoryController@pruebas');
    //Route::get('/entrada/pruebas', 'PostController@pruebas');

    //RUTAS DEL CONTROLADOR DE USUARIOS
    Route::post('/api/register', 'UserController@register');
    Route::post('/api/login', 'UserController@login');
    Route::put('/api/user/update', 'UserController@update');
    Route::post('/api/user/upload', 'UserController@upload') -> middleware(ApiAuthMiddleware::class);
    Route::get('/api/user/avatar/{filename}', 'UserController@getImage');
    Route::get('/api/user/detail/{id}', 'UserController@detail');

    // Rutas del controlodor de categorias
    Route::resource('/api/category', 'CategoryController');

    // Rutas del controlador de entradas
    Route::resource('/api/post', 'PostController');
    Route::post('/api/post/upload', 'PostController@upload');
    Route::get('/api/post/image/{filename}', 'PostController@getImage');
    Route::get('/api/post/category/{id}', 'PostController@getPostsByCategory');
    Route::get('/api/post/user/{id}', 'PostController@getPostsByUser');