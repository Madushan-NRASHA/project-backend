<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\JobController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Auth routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);
Route::post('/update-theme', [AuthController::class, 'updateTheme']);
Route::post('/update-profile', [AuthController::class, 'updateProfile']);

// User routes (without additional prefix since this is already in api.php)
Route::get('/users', [UserController::class, 'index']);
Route::get('/users/{id}', [UserController::class, 'show']);
Route::get('/users/paginated', [UserController::class, 'getUsersPaginated']);
Route::get('/users/search', [UserController::class, 'search']);
Route::post('/users', [UserController::class, 'store']);
Route::put('/users/{id}', [UserController::class, 'update']);
Route::put('/users/{id}/user-type', [UserController::class, 'updateUserType']);
Route::delete('/users/{id}', [UserController::class, 'destroy']);


// Jon api End Points 
Route::prefix('jobs')->group(function () {
    Route::get('/', [JobController::class, 'index']); // GET /api/jobs
    Route::post('/', [JobController::class, 'store']); // POST /api/jobs
    Route::get('/{id}', [JobController::class, 'show']); // GET /api/jobs/{id}
    Route::put('/{id}', [JobController::class, 'update']); // PUT /api/jobs/{id}
    Route::delete('/{id}', [JobController::class, 'destroy']); // DELETE /api/jobs/{id}
});