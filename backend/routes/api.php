<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\CharitiesController;
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

Route::post('/register',[UsersController::class,'register']);
Route::post('/login',[UsersController::class,'login']);
Route::get('/dashboard',[UsersController::class,'dashboard']);
Route::get('/logout',[UsersController::class,'logout']);
Route::post('/charity/register',[CharitiesController::class,'register']);
Route::post('/charity/login',[CharitiesController::class,'login']);
Route::get('/charity/dashboard',[CharitiesController::class,'dashboard']);
Route::get('/charity/logout',[CharitiesController::class,'logout']);