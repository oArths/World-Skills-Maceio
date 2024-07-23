<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AllController;
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
    Route::delete('/machines/{id?}', [AllController::class, 'removeMachine'])->middleware('jwt');
    Route::get('/images/{id?}', [AllController::class, 'getImages'])->middleware('jwt');
    Route::get('/list', [AllController::class, 'ListItems'])->middleware('jwt');

    Route::get('/list/motherboards', [AllController::class, 'Listmotherboards'])->middleware('jwt');
    Route::get('/list/processor', [AllController::class, 'Listprocessor'])->middleware('jwt');
    Route::get('/list/rammemory', [AllController::class, 'Listrammemory'])->middleware('jwt');
    Route::get('/list/storagedevice', [AllController::class, 'Liststoragedevice'])->middleware('jwt');
    Route::get('/list/graphiccard', [AllController::class, 'Listgraphiccard'])->middleware('jwt');
    Route::get('/list/powersupply', [AllController::class, 'Listpowersupply'])->middleware('jwt');
    Route::get('/list/brand', [AllController::class, 'Listbrand'])->middleware('jwt');
    Route::get('/list/machine', [AllController::class, 'Listmachine'])->middleware('jwt');


    Route::get('/search/{category?}/{q?}/{pagesize?}/{page?}', [AllController::class, 'Searchitem'])->middleware('jwt');
    Route::post('/verifyCompatibility', [AllController::class, 'VerifyComp'])->middleware('jwt');
    Route::any('/{any}', function () {
        return response()->json([], 403);
    })->middleware('jwt');
});
