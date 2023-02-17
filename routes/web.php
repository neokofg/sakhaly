<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::get('/user/{json}',[\App\Http\Controllers\AuthApiController::class, 'registerUser']);
Route::get('/login/{json}',[\App\Http\Controllers\AuthApiController::class, 'loginUser']);
Route::get('/allUsers',[\App\Http\Controllers\AuthApiController::class,'getAllUsers']);

Route::get('/newGroup/{json}',[\App\Http\Controllers\GroupsApiController::class, 'newGroup']);
Route::get('/addUserToGroup/{json}',[\App\Http\Controllers\GroupsApiController::class, 'addUserToGroup']);
Route::get('/deleteUserFromGroup/{json}',[\App\Http\Controllers\GroupsApiController::class, 'deleteUserFromGroup']);
Route::get('/getGroup/{json}',[\App\Http\Controllers\GroupsApiController::class, 'getGroup']);
Route::get('/getGroups',[\App\Http\Controllers\GroupsApiController::class, 'getGroups']);

Route::get('/updateUser/{json}',[\App\Http\Controllers\UserApiController::class,'updateUser']);
Route::get('/updateStat/{json}',[\App\Http\Controllers\UserApiController::class,'updateStat']);

Route::get('/createRoom/{json}',[\App\Http\Controllers\RoomApiController::class,'createRoom']);
Route::get('/joinRoom/{json}',[\App\Http\Controllers\RoomApiController::class,'joinRoom']);
Route::get('/leaveRoom/{json}',[\App\Http\Controllers\RoomApiController::class,'leaveRoom']);
Route::get('/getRoom/{json}',[\App\Http\Controllers\RoomApiController::class,'getRoom']);
Route::get('/startRoom/{json}',[\App\Http\Controllers\RoomApiController::class,'startRoom']);
Route::get('/userAnswer/{json}',[\App\Http\Controllers\RoomApiController::class,'userAnswer']);
Route::get('/finishRoom/{json}',[\App\Http\Controllers\RoomApiController::class,'finishRoom']);

Route::get('/test',[\App\Http\Controllers\RoomApiController::class,'Test']);
