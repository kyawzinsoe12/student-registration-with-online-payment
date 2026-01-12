<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\UserController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\LessonController;
use App\Http\Controllers\Api\MajorController;
use App\Http\Controllers\Api\RoleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/test',function(){
    return "hello this is test";
});

    //Auth
Route::post('/register',[AuthController::class,'register']);
Route::post('/login',[AuthController::class,'login']);
Route::post('/forgotPassword',[AuthController::class,'verifyResetPasswordOtp']);
Route::post('/resetPassword',[AuthController::class,'resetPassword']);

Route::middleware(['auth:sanctum'])->group(function () {

    // logout
    Route::post('/logout',[AuthController::class,'logout']);

    // Role and permission
    Route::get('/roles',[RoleController::class,'index']);
    Route::post('/roles',[RoleController::class,'store']);
    Route::put('roles/{role}',[RoleController::class,'update']);
    Route::delete('roles/{role}', [RoleController::class, 'destroy']);

    //assign user to role and permission
    Route::post('users/{user}/assignRoleAndPermission',[UserController::class,'assignRoleAndPermission']);

    //majors
    Route::get('majors',[MajorController::class,'index']);
    Route::post('majors',[MajorController::class,'store']);
    Route::put('majors/{major}',[MajorController::class,'update']);
    Route::delete('majors/{major}',[MajorController::class,'destroy']);
    //Courses
    Route::get('/courses',[CourseController::class,'index']);
    Route::get('courses/{course}',[CourseController::class,'show']);
    Route::post('/courses',[CourseController::class,'store']);
    Route::put('courses/{course}',[CourseController::class,'update']);
    Route::delete('courses/{course}',[CourseController::class,'destroy']);

    //Lessons
    Route::get('lessons',[LessonController::class,'index']);
    Route::get('lessons/{lesson}',[LessonController::class,'show']);
    Route::post('courses/{course}/lessons',[LessonController::class,'store']);
    Route::put('lessons/{lesson}',[LessonController::class,'update']);
    Route::delete('lessons/{lesson}',[LessonController::class,'destroy']);
    
});