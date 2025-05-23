<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\EmailAccountController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

// Authentication routes
require __DIR__.'/auth.php';

// Protected routes
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Account Management Routes - available to both admins and account managers
    Route::middleware(['account.manager'])->prefix('accounts')->name('accounts.')->group(function () {
        // MEXC Accounts
        Route::get('/mexc', [AccountController::class, 'mexcIndex'])->name('mexc');
        Route::get('/mexc/create', [AccountController::class, 'mexcCreate'])->name('mexc.create');
        Route::post('/mexc', [AccountController::class, 'mexcStore'])->name('mexc.store');
        Route::get('/mexc/{mexcAccount}/edit', [AccountController::class, 'mexcEdit'])->name('mexc.edit');
        Route::put('/mexc/{mexcAccount}', [AccountController::class, 'mexcUpdate'])->name('mexc.update');
        Route::delete('/mexc/{mexcAccount}', [AccountController::class, 'mexcDestroy'])->name('mexc.destroy');

        // Email Accounts (Resource route)
        Route::resource('email', EmailAccountController::class)->names([
            'index' => 'email',
            'create' => 'email.create',
            'store' => 'email.store',
            'edit' => 'email.edit',
            'update' => 'email.update',
            'destroy' => 'email.destroy',
        ]);



        // Proxies
        Route::get('/proxy', [AccountController::class, 'proxyIndex'])->name('proxy');
        Route::get('/proxy/create', [AccountController::class, 'proxyCreate'])->name('proxy.create');
        Route::post('/proxy', [AccountController::class, 'proxyStore'])->name('proxy.store');
        Route::get('/proxy/{proxy}/edit', [AccountController::class, 'proxyEdit'])->name('proxy.edit');
        Route::put('/proxy/{proxy}', [AccountController::class, 'proxyUpdate'])->name('proxy.update');
        Route::delete('/proxy/{proxy}', [AccountController::class, 'proxyDestroy'])->name('proxy.destroy');

        // Web3 Wallets
        Route::get('/web3', [AccountController::class, 'web3Index'])->name('web3');
        Route::get('/web3/create', [AccountController::class, 'web3Create'])->name('web3.create');
        Route::post('/web3', [AccountController::class, 'web3Store'])->name('web3.store');
        Route::get('/web3/{web3Wallet}/edit', [AccountController::class, 'web3Edit'])->name('web3.edit');
        Route::put('/web3/{web3Wallet}', [AccountController::class, 'web3Update'])->name('web3.update');
        Route::delete('/web3/{web3Wallet}', [AccountController::class, 'web3Destroy'])->name('web3.destroy');
    });

    // Relationships and Validation
    Route::get('/relationships', [AccountController::class, 'relationships'])->name('relationships');
    Route::get('/validation', [AccountController::class, 'validation'])->name('validation');
    Route::post('/validation/run', [AccountController::class, 'runValidation'])->name('validation.run');

    // Admin Only Routes
    Route::middleware(['admin'])->prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', AdminController::class);
        Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
        Route::post('/settings', [AdminController::class, 'updateSettings'])->name('settings.update');
    });
});