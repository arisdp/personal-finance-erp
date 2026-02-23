<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\JournalController;
use App\Http\Controllers\LedgerController;
use App\Http\Controllers\ReportController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::resource('accounts', AccountController::class);
    Route::resource('journals', JournalController::class);

    Route::get('ledger', [LedgerController::class, 'index'])
        ->name('ledger.index');

    Route::get('trial-balance', [ReportController::class, 'trialBalance'])
        ->name('reports.trial');
});
Route::middleware(['auth', 'role:super_admin'])->group(function () {

    Route::get('/admin/users', function () {
        return view('admin.users');
    })->name('admin.users');
});

require __DIR__ . '/auth.php';
