<?php

use App\Http\Middleware\ApiAuthMiddleware;
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

Route::get('/', function () {
    return view('welcome');
});

//Rutas de prueba
Route::get('/pruebaOrm', 'PruebasController@pruebaOrm');

//Rutas controlador usuarios
Route::post('/api/register', 'UserController@register');
Route::post('/api/login', 'UserController@login');
Route::put('/api/user/update', 'UserController@update')->middleware(ApiAuthMiddleware::class);
Route::post('/api/user/upload', 'UserController@upload')->middleware(ApiAuthMiddleware::class);
Route::get('/api/user/getImage/{filename}', 'UserController@getImage');
Route::get('/api/user/detail/{id}', 'UserController@detail');


//Rutas controlador de posts
Route::resource('api/post', 'PostController');
Route::post('/api/post/upload', 'PostController@upload');
Route::get('/api/post/image/{filename}', 'PostController@getImage');
Route::get('/api/post/user/{id}', 'PostController@getPostsByUser');