<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\JobController;
use App\Http\Controllers\JobFilterController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\MsgController;
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
    Route::get('/filter', [JobController::class, 'getJobs']); // GET /api/jobs/filter
    Route::post('/', [JobController::class, 'store']); 
    Route::get('/{id}', [JobController::class, 'show']); 
    Route::put('/{id}', [JobController::class, 'update']); 
    Route::delete('/{id}', [JobController::class, 'destroy']); 
});


Route::get('/jobs/filter', [JobFilterController::class, 'apiIndex']);
Route::post('/jobs/filter', [JobFilterController::class, 'filter']);
Route::get('/jobs/categories', [JobFilterController::class, 'getCategories']);
Route::get('/jobs/category/{category}', [JobFilterController::class, 'getByCategory']);
Route::get('/jobs/stats', [JobFilterController::class, 'getStats']);


Route::prefix('projects')->group(function () {
    Route::get('/', [ProjectController::class, 'index']);       // GET /api/projects
    Route::post('/', [ProjectController::class, 'store']);      // POST /api/projects
    Route::get('{id}', [ProjectController::class, 'show']);     // GET /api/projects/{id}
    Route::put('{id}', [ProjectController::class, 'update']);   // PUT /api/projects/{id}
    Route::delete('{id}', [ProjectController::class, 'destroy']); // DELETE /api/projects/{id}
     Route::get('/user-projects/{userId}', [ProjectController::class, 'getUserProjects']);
    Route::get('/my-projects', [ProjectController::class, 'getMyProjects']);
});


Route::post('/store-message', [MsgController::class, 'store']);