<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\CharitiesController;
use App\Http\Controllers\EventsController;
use App\Http\Controllers\CommentsController;


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
Route::get('/charity/profile/name/{name}', [CharitiesController::class, 'profile']);
Route::get('/profile/name/{name}', [UsersController::class, 'profile']);
Route::middleware(['auth:charity'])->group(function () {
    Route::apiResource('events', EventsController::class);
});
Route::middleware(['auth:user'])->group(function () {
    Route::apiResource('comments', CommentsController::class,['store']);
});
Route::middleware(['auth:user'])->group(function () {
    Route::post('/dashboard/comments/store', [CommentsController::class,'store']);
});
Route::middleware(['auth:charity'])->group(function () {
    Route::get('/my-events', [EventsController::class, 'myEvents']);
});
Route::post('/events/{eventId}/verified', [EventsController::class, 'verifyUserAttendance']);
Route::get('/all-events', [EventsController::class, 'allEvents']);
Route::middleware(['auth:user'])->get('/charity-events/{charityName}', [EventsController::class, 'eventsByCharityName']);
Route::middleware(['auth:charity'])->post('/charity/events', [EventsController::class, 'store']);
Route::middleware(['auth:charity'])->delete('/event/destroy/{id}', [EventsController::class, 'destroy']);
Route::middleware('auth:user')->group(function () {
    Route::post('/events/{eventId}/interest', [EventsController::class, 'markUserInterestedEvent']);
    Route::post('/events/{eventId}/going', [EventsController::class, 'markUserGoingToEvent']);
    Route::get('/events/interested', [EventsController::class, 'listInterestedEvents']);
    Route::get('/events/going', [EventsController::class, 'listGoingEvents']);
});
