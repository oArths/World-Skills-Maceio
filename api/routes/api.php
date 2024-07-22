<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Machine;
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

Route::prefix('AlatechMachines/api')->group(function(){
    Route::post('/login', [UserController::class, 'login']);
    Route::delete('/delete', [UserController::class, 'logOutUser'])->middleware('jwt');
    Route::get('/images/{id?}', [Machine::class, 'getImages']);
});
