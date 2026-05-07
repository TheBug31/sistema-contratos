<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ContractController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OperationController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ContractStatusRequestController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TwoFactorController;

/*
|--------------------------------------------------------------------------
| Públicas
|--------------------------------------------------------------------------
*/

Route::get('/', fn() => redirect()->route('login'));
Route::get('/login',  [LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.attempt');

    /*
    |--------------------------------------------------------------------------
    | Profile
    |--------------------------------------------------------------------------
    */
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    /*
    |--------------------------------------------------------------------------
    | 2FA (Two-Factor Authentication)
    |--------------------------------------------------------------------------
    */
    Route::prefix('2fa')->name('2fa.')->middleware('auth')->group(function () {
        Route::get('/', [TwoFactorController::class, 'show'])->name('show');
        Route::post('/enable', [TwoFactorController::class, 'enable'])->name('enable');
        Route::post('/confirm', [TwoFactorController::class, 'confirm'])->name('confirm');
        Route::post('/disable', [TwoFactorController::class, 'disable'])->name('disable');
        Route::post('/recovery-codes', [TwoFactorController::class, 'recoveryCodes'])->name('recovery-codes');
    });

    /*
    |--------------------------------------------------------------------------
    | Contratos — accesibles por todos los roles
    |--------------------------------------------------------------------------
    */
    Route::prefix('contracts')->name('contracts.')->group(function () {

        // ── Rutas estáticas PRIMERO (antes de cualquier {contract}) ──
        Route::get('/',       [ContractController::class, 'index'])->name('index');

        Route::middleware('role:Administrador|Secretario')->group(function () {
            Route::get('/create', [ContractController::class, 'create'])->name('create');
            Route::post('/',      [ContractController::class, 'store'])->name('store');
        });

        // ── Rutas con parámetro {contract} ──
        Route::get('/{contract}',         [ContractController::class, 'show'])->name('show');
        Route::get('/{contract}/history', [ContractController::class, 'history'])->name('history');
        Route::get('/{contract}/pdf',     [ContractController::class, 'generatePdf'])->name('pdf');

        // Solo el Asesor solicita cambios
        Route::post('/{contract}/requests', [ContractStatusRequestController::class, 'store'])
            ->name('requests.store')
            ->middleware('role:Asesor');

        // Solo el Asesor acepta o rechaza contratos asignados
        Route::post('/{contract}/accept', [ContractController::class, 'accept'])
            ->name('accept')
            ->middleware('role:Asesor');
        Route::post('/{contract}/reject', [ContractController::class, 'reject'])
            ->name('reject')
            ->middleware('role:Asesor');

        // Solo Admin / Secretario / Gerente gestionan estado y reasignación
        Route::middleware('role:Administrador|Secretario|Gerente')->group(function () {
            Route::put('/{contract}/status',   [ContractController::class, 'updateStatus'])->name('update-status');
            Route::put('/{contract}/reassign', [ContractController::class, 'updateReassign'])->name('reassign');
        });

        // Operaciones masivas (solo Admin / Secretario / Gerente)
        Route::middleware('role:Administrador|Secretario|Gerente')->group(function () {
            Route::post('/bulk/status',   [ContractController::class, 'bulkStatusUpdate'])->name('bulk-status');
            Route::post('/bulk/reassign', [ContractController::class, 'bulkReassign'])->name('bulk-reassign');
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Solicitudes de cambio de estado
    |--------------------------------------------------------------------------
    */
    Route::prefix('contract-requests')->name('contract-requests.')->middleware('role:Administrador|Secretario|Gerente')->group(function () {
        Route::get('/',                          [ContractStatusRequestController::class, 'index'])->name('index');
        Route::patch('/{statusRequest}/approve', [ContractStatusRequestController::class, 'approve'])->name('approve');
        Route::patch('/{statusRequest}/reject',  [ContractStatusRequestController::class, 'reject'])->name('reject');
    });

    /*
    |--------------------------------------------------------------------------
    | Usuarios
    |--------------------------------------------------------------------------
    */
    Route::resource('users', UserController::class)
        ->middleware('role:Administrador|Secretario|Gerente');

    Route::patch('/users/{user}/toggle', [UserController::class, 'toggle'])
        ->name('users.toggle')
        ->middleware('role:Administrador|Secretario|Gerente');

    Route::patch('/users/{user}/toggle', [UserController::class, 'toggle'])
        ->name('users.toggle')
        ->middleware('role:Administrador|Secretario|Gerente');

    /*
    |--------------------------------------------------------------------------
    | Operaciones
    |--------------------------------------------------------------------------
    */
    Route::resource('operations', OperationController::class)
        ->middleware('role:Administrador');

    /*
    |--------------------------------------------------------------------------
    | Auditoría
    |--------------------------------------------------------------------------
    */
    Route::get('/audits', [AuditController::class, 'index'])
        ->name('audits.index')
        ->middleware('role:Administrador');

    /*
    |--------------------------------------------------------------------------
    | Reportes
    |--------------------------------------------------------------------------
    */
    Route::prefix('reports')->name('reports.')->middleware('auth')->group(function () {
        // Contratos
        Route::get('/contracts/pdf', [ReportController::class, 'contractsPdf'])->name('contracts.pdf');
        Route::get('/contracts/excel', [ReportController::class, 'contractsExcel'])->name('contracts.excel');

        // Auditoría
        Route::get('/audits/pdf', [ReportController::class, 'auditsPdf'])
            ->name('audits.pdf')
            ->middleware('role:Administrador');
        Route::get('/audits/excel', [ReportController::class, 'auditsExcel'])
            ->name('audits.excel')
            ->middleware('role:Administrador');

        // Dashboard
        Route::get('/dashboard/pdf', [ReportController::class, 'dashboardPdf'])->name('dashboard.pdf');
    });
