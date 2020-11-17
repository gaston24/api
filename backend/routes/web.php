<?php

use Illuminate\Support\Facades\Route;
use App\Http\Middleware\ApiAuthMiddleware;

Route::get('/', function () {
    return "hola mundo";
});

Route::get('/testOrm', 'PruebasController@testOrm');

//API
// Route::get('/post/prueba', 'PostController@pruebas');

// Route::get('/category/prueba', 'CategoryController@pruebas');

Route::get('/api/user/prueba', 'UserController@pruebas');
Route::post('/api/user/login', 'UserController@login');
Route::post('/api/user/register', 'UserController@register');
Route::put('/api/user/update', 'UserController@update');
Route::post('/api/user/upload', 'UserController@upload')->middleware(ApiAuthMiddleware::class);
Route::get('/api/user/avatar/{filename}', 'UserController@getImage');
Route::get('/api/user/detail/{id}', 'UserController@detail');

// Rutas Category
Route::resource('/api/category', 'CategoryController');

// Rutas Posts
Route::resource('/api/post', 'PostController');
Route::post('/api/post/upload', 'PostController@upload');
Route::get('/api/post/image/{filename}', 'PostController@getImage');
Route::get('/api/post/category/{id}', 'PostController@getPostsByCategory');
Route::get('/api/post/user/{id}', 'PostController@getPostsByUser');
