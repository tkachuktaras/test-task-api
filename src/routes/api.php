<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;


Route::controller(UserController::class)->group(function () {
    Route::middleware('auth:sanctum')->prefix('user')->group(function () {
        Route::get('/', 'index');
        Route::prefix('file')->group(function () {
            Route::get('/{file_id?}', 'fileInfo');
            Route::delete('/{file_id}', 'deleteFile');
            Route::post('/upload', 'uploadFile');
        });
    });

    Route::post('/register', 'register');
    Route::post('/login', 'login');
    Route::post('/logout', 'logout');
});


