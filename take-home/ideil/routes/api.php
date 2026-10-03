<?php

use App\Marker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// markers api
Route::get('markers', 'MarkerController@index');
Route::get('markers/{id}', 'MarkerController@show');
Route::post('markers', 'MarkerController@store');
Route::put('markers/{id}', 'MarkerController@update');
Route::delete('markers/{id}', 'MarkerController@destroy');

// comments api
Route::get('comments', 'CommentController@index');
Route::get('comments/{id}', 'CommentController@show');
Route::post('comments', 'CommentController@store');
Route::put('comments/{id}', 'CommentController@update');
Route::delete('comments/{id}', 'CommentController@destroy');

// photos api
Route::get('photos', 'PhotoController@index');
Route::get('photos/{id}', 'PhotoController@show');
Route::post('photos', 'PhotoController@store');
Route::put('photos/{id}', 'PhotoController@update');
Route::delete('photos/{id}', 'PhotoController@destroy');

// users api
Route::get('users', 'UserController@index');
Route::get('users/{id}', 'UserController@show');
Route::get('users/{id}/promote', 'UserController@promote');
Route::get('users/{id}/markers', 'UserController@markers');
Route::get('users/{id}/comments', 'UserController@comments');
Route::get('users/{id}/photos', 'UserController@photos');
Route::delete('users/{id}', 'UserController@destroy');

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});
