<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\RoleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/test',function(){
    return "hello this is test";
});

Route::post('/register',[AuthController::class,'register']);
Route::post('/login',[AuthController::class,'login']);
Route::post('/forgotPassword',[AuthController::class,'verifyResetPasswordOtp']);
Route::post('/resetPassword',[AuthController::class,'resetPassword']);

// Route::get('/roles',[RoleController::class,'index']);
// Route::post('/roles/create',[RoleController::class,'store']);
// Route::put('roles/{role}',[RoleController::class,'update']);
// Route::delete('roles/{role}', [RoleController::class, 'destroy']);

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/roles',[RoleController::class,'index']);
    Route::post('/roles/create',[RoleController::class,'store']);
    Route::put('roles/{role}',[RoleController::class,'update']);
    Route::delete('roles/{role}', [RoleController::class, 'destroy']);
});