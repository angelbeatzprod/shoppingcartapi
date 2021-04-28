<?php

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

Route::middleware('auth:api')->get('products', 'App\Http\Controllers\ProductsController@index');
Route::middleware('auth:api')->get('products/{id}', 'App\Http\Controllers\ProductsController@show');
Route::middleware('auth:api')->post('products', 'App\Http\Controllers\ProductsController@store');
Route::middleware('auth:api')->put('products/{id}', 'App\Http\Controllers\ProductsController@update');
Route::middleware('auth:api')->delete('products/{id}', 'App\Http\Controllers\ProductsController@destroy');

Route::middleware('auth:api')->get('cart/user/{user_id}', 'App\Http\Controllers\CartController@index');
Route::middleware('auth:api')->get('cart/user/{user_id}/item/{item_id}', 'App\Http\Controllers\CartController@show');
Route::middleware('auth:api')->post('cart/user/{user_id}', 'App\Http\Controllers\CartController@store');
Route::middleware('auth:api')->delete('cart/user/{user_id}/item/{item_id}', 'App\Http\Controllers\CartController@destroy');

Route::post('user/login', 'App\Http\Controllers\UserController@login');
Route::post('user/signup', 'App\Http\Controllers\UserController@signup');
