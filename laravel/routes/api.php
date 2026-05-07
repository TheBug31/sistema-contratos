<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ContractController;
use App\Http\Controllers\Api\DashboardController;

/*
|--------------------------------------------------------------------------
| Public
|--------------------------------------------------------------------------
*/

Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:5,1');

/*
|--------------------------------------------------------------------------
| Protected (Sanctum)
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->name('api.')->group(function () {

    /*
    | Auth
    */
    Route::post('/logout', [AuthController::class, 'logout'])
        ->name('logout');

    /*
    | Dashboard
    */
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    /*
    | Users
    */
    Route::apiResource('users', UserController::class);

    /*
    | Contracts - API RESTful completa
    */
    Route::get('/contracts', [ContractController::class, 'index'])->name('contracts.index');
    Route::post('/contracts', [ContractController::class, 'store'])->name('contracts.store');
    Route::get('/contracts/{contract}', [ContractController::class, 'show'])->name('contracts.show');
    Route::put('/contracts/{contract}', [ContractController::class, 'update'])->name('contracts.update');
    Route::delete('/contracts/{contract}', [ContractController::class, 'destroy'])->name('contracts.destroy');
    
    // Acciones especiales
    Route::get('/contracts/{contract}/history', [ContractController::class, 'history'])->name('contracts.history');
    Route::post('/contracts/assign', [ContractController::class, 'assign'])->name('contracts.assign');
    Route::put('/contracts/{contract}/status', [ContractController::class, 'changeStatus'])->name('contracts.changeStatus');
    Route::put('/contracts/{contract}/reassign', [ContractController::class, 'reassign'])->name('contracts.reassign');
    Route::post('/contracts/{contract}/accept', [ContractController::class, 'accept'])->name('contracts.accept');
    Route::post('/contracts/{contract}/reject', [ContractController::class, 'reject'])->name('contracts.reject');
});

/*
|--------------------------------------------------------------------------
| API Documentation
|--------------------------------------------------------------------------
*/

Route::get('/documentation', function () {
    return view('swagger');
});

Route::get('/swagger.json', function () {
    $path = storage_path('api-docs/swagger.json');
    
    if (!file_exists($path)) {
        abort(404);
    }
    
    return response()->json(
        json_decode(file_get_contents($path), true)
    );
});
