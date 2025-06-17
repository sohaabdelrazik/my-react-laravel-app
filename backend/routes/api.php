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
Route::get('/profile/name/{name}', [UsersController::class, 'profile']);
Route::get('/top-rated',[UsersController::class,'topRated']);

Route::post('/charity/register',[CharitiesController::class,'register']);
Route::post('/charity/login',[CharitiesController::class,'login']);
Route::get('/charity/dashboard',[CharitiesController::class,'dashboard']);
Route::get('/charity/logout',[CharitiesController::class,'logout']);
Route::get('/charity/profile/name/{name}', [CharitiesController::class, 'profile']);

Route::get('/show/{eventId}/comments', [CommentsController::class,'showAll']);
Route::delete('/comment/destroy/{id}', [CommentsController::class, 'destroy']);
Route::post('/create/comment', [CommentsController::class, 'store']);
Route::put('/update/comment/{id}', [CommentsController::class, 'update']);
Route::get('/show/comment/{id}', [CommentsController::class,'show']);

Route::middleware(['auth:charity'])->get('/my-events', [EventsController::class, 'myEvents']);
Route::post('/events/{userId}/verified', [EventsController::class, 'verifyUserAttendance']);
Route::get('/all-events', [EventsController::class, 'allEvents']);
Route::middleware(['auth:user'])->get('/charity-events/{charityName}', [EventsController::class, 'eventsByCharityName']);
Route::middleware(['auth:charity'])->post('/charity/create/event', [EventsController::class, 'store']);
Route::middleware(['auth:charity'])->delete('/event/destroy/{id}', [EventsController::class, 'destroy']);
Route::middleware('auth:user')->group(function () {
    Route::post('/events/{eventId}/interest', [EventsController::class, 'markUserInterestedEvent']);
    Route::post('/events/{eventId}/going', [EventsController::class, 'markUserGoingToEvent']);
    Route::post('/events/interestedList', [EventsController::class, 'listInterestedEvents']);
    Route::post('/events/goingList', [EventsController::class, 'listGoingEvents']);});
Route::get('/going/{eventId}/users', [EventsController::class, 'listGoingUsers']);
Route::put('/update/event/{eventId}', [EventsController::class, 'update']);
Route::get('/show/event/{eventId}', [EventsController::class,'show']);
Route::post('/block/user/{userId}', [EventsController::class, 'blockUser']);
Route::post('/unblock/user/{userId}', [EventsController::class, 'unblockUser']);
Route::get('/top-charities', [EventsController::class, 'topCharitiesByEvents']);

