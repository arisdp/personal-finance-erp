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
    Route::resource('budgets', BudgetController::class);

    Route::get('ledger', [LedgerController::class, 'index'])
        ->name('ledger.index');
    Route::get('ledger/download-pdf', [LedgerController::class, 'downloadPdf'])
        ->name('ledger.pdf');

    Route::get('trial-balance', [ReportController::class, 'trialBalance'])
        ->name('reports.trial');
    Route::get('trial-balance/download-pdf', [ReportController::class, 'trialBalancePdf'])
        ->name('reports.trial.pdf');

    Route::get('profit-loss', [ReportController::class, 'profitAndLoss'])
        ->name('reports.pl');
    Route::get('profit-loss/download-pdf', [ReportController::class, 'profitAndLossPdf'])
        ->name('reports.pl.pdf');

    // Investment Routes
    Route::get('investments', [\App\Http\Controllers\AssetController::class, 'index'])->name('investments.index');
    Route::get('investments/create', [\App\Http\Controllers\AssetController::class, 'create'])->name('investments.create');
    Route::post('investments', [\App\Http\Controllers\AssetController::class, 'store'])->name('investments.store');
    Route::patch('investments/{investment}/price', [\App\Http\Controllers\AssetController::class, 'updatePrice'])->name('investments.updatePrice');
    Route::delete('investments/{investment}', [\App\Http\Controllers\AssetController::class, 'destroy'])->name('investments.destroy');

    // Workspace Routes
    Route::resource('workspaces', \App\Http\Controllers\WorkspaceController::class);
    Route::post('workspaces/{workspace}/switch', [\App\Http\Controllers\WorkspaceController::class, 'switch'])->name('workspaces.switch');
    Route::post('workspaces/{workspace}/members', [\App\Http\Controllers\WorkspaceController::class, 'addMember'])->name('workspaces.addMember');
    Route::patch('workspaces/{workspace}/members/{user}', [\App\Http\Controllers\WorkspaceController::class, 'updateRole'])->name('workspaces.updateRole');
    Route::delete('workspaces/{workspace}/members/{user}', [\App\Http\Controllers\WorkspaceController::class, 'removeMember'])->name('workspaces.removeMember');
});

Route::middleware(['auth', 'role:super_admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
    });

Route::middleware('auth')->group(function () {
    Route::get('/profile', function () {
        return view('profile.edit');
    })->name('profile.edit');
});
require __DIR__ . '/auth.php';
