<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\VoterController;
use App\Http\Controllers\Api\WardController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\PanchayatController;
use App\Http\Controllers\Api\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::post('/auth/login', [AuthController::class, 'login'])->name('api.auth.login');

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/auth/logout', [AuthController::class, 'logout'])->name('api.auth.logout');
    Route::get('/auth/user', [AuthController::class, 'user'])->name('api.auth.user');

    // Dashboard route (all authenticated users)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('api.dashboard.index');

    // Panchayats routes (Superadmin & Team Lead)
    Route::middleware('role:superadmin|team_lead')->group(function () {
        Route::apiResource('panchayats', PanchayatController::class);
    });

    // Wards routes (Superadmin & Team Lead)
    Route::middleware('role:superadmin|team_lead')->group(function () {
        Route::apiResource('wards', WardController::class);
        Route::get('panchayats/{panchayat}/wards', [WardController::class, 'getByPanchayat'])->name('api.panchayats.wards');
    });

    // Users routes (Superadmin only)
    Route::middleware('role:superadmin')->group(function () {
        Route::apiResource('users', UserController::class);
    });

    // Voters routes
    Route::prefix('voters')->group(function () {
        Route::get('/', [VoterController::class, 'index'])->name('api.voters.index');
        Route::get('/find-by-serial', [VoterController::class, 'findBySerialNumber'])->name('api.voters.find-by-serial');
        Route::get('/unassigned', [VoterController::class, 'getUnassignedVoters'])->name('api.voters.unassigned');
        // Route::get('/assigned', [VoterController::class, 'getAssignedVoters'])->name('api.voters.assigned');
        Route::get('/{voter}', [VoterController::class, 'show'])->name('api.voters.show');
        Route::get('/worker-assigned', [VoterController::class, 'getAssignedVoters'])->name('api.voters.assigned');

        // Superadmin only
        Route::middleware('role:superadmin')->group(function () {
            Route::post('/', [VoterController::class, 'store'])->name('api.voters.store');
            Route::put('/{voter}', [VoterController::class, 'update'])->name('api.voters.update');
            Route::delete('/{voter}', [VoterController::class, 'destroy'])->name('api.voters.destroy');
        });

        // Team Lead, Booth Agent, Superadmin, and Workers can update status
        Route::middleware('role:team_lead|booth_agent|superadmin|worker')->group(function () {
            Route::patch('/{voter}/status', [VoterController::class, 'updateStatus'])->name('api.voters.update-status');
        });

        // Worker can update remark
        Route::middleware('role:worker')->group(function () {
            Route::patch('/{voter}/remark', [VoterController::class, 'updateRemark'])->name('api.voters.update-remark');
        });

        // Team Lead or Superadmin can assign voters to workers
        Route::middleware('role:team_lead|superadmin')->group(function () {
            Route::post('/bulk-assign', [VoterController::class, 'bulkAssignWorker'])->name('api.voters.bulk-assign');
            Route::post('/{voter}/assign', [VoterController::class, 'assignWorker'])->name('api.voters.assign-worker');
            Route::delete('/{voter}/assign', [VoterController::class, 'unassignWorker'])->name('api.voters.unassign-worker');
        });
    });
});
