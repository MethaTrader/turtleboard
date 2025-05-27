<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\EmailAccountController;
use App\Http\Controllers\EmailGeneratorController;
use App\Http\Controllers\MexcAccountController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProxyController;
use App\Http\Controllers\Web3WalletController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/


Route::get('/api/generate-email', [EmailGeneratorController::class, 'generate']);

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
        Route::get('/mexc', [MexcAccountController::class, 'index'])->name('mexc');
        Route::get('/mexc/create', [MexcAccountController::class, 'create'])->name('mexc.create');
        Route::post('/mexc', [MexcAccountController::class, 'store'])->name('mexc.store');
        Route::get('/mexc/{mexcAccount}/edit', [MexcAccountController::class, 'edit'])->name('mexc.edit');
        Route::put('/mexc/{mexcAccount}', [MexcAccountController::class, 'update'])->name('mexc.update');
        Route::delete('/mexc/{mexcAccount}', [MexcAccountController::class, 'destroy'])->name('mexc.destroy');
        Route::get('/mexc/{mexcAccount}/credentials', [MexcAccountController::class, 'credentials'])->name('mexc.credentials');

        // Email Accounts with explicit route names to match parameter names in controller
        Route::get('/email', [EmailAccountController::class, 'index'])->name('email');
        Route::get('/email/create', [EmailAccountController::class, 'create'])->name('email.create');
        Route::post('/email', [EmailAccountController::class, 'store'])->name('email.store');
        Route::get('/email/{email}/edit', [EmailAccountController::class, 'edit'])->name('email.edit');
        Route::put('/email/{email}', [EmailAccountController::class, 'update'])->name('email.update');
        Route::delete('/email/{email}', [EmailAccountController::class, 'destroy'])->name('email.destroy');

        Route::get('/email/{emailAccount}/credentials', [EmailAccountController::class, 'credentials'])->name('email.credentials');


        // Proxy routes
        Route::get('/proxy', [ProxyController::class, 'index'])->name('proxy');
        Route::get('/proxy/create', [ProxyController::class, 'create'])->name('proxy.create');
        Route::post('/proxy', [ProxyController::class, 'store'])->name('proxy.store');
        Route::get('/proxy/{proxy}/edit', [ProxyController::class, 'edit'])->name('proxy.edit');
        Route::put('/proxy/{proxy}', [ProxyController::class, 'update'])->name('proxy.update');
        Route::delete('/proxy/{proxy}', [ProxyController::class, 'destroy'])->name('proxy.destroy');

        // Proxy validation routes
        Route::post('/proxy/{proxy}/validate', [ProxyController::class, 'validate'])->name('proxy.validate');
        Route::post('/proxy/validate-all', [ProxyController::class, 'runValidation'])->name('proxy.validate-all');

// Web3 Wallets
        Route::get('/web3', [Web3WalletController::class, 'index'])->name('web3');
        Route::get('/web3/create', [Web3WalletController::class, 'create'])->name('web3.create');
        Route::post('/web3', [Web3WalletController::class, 'store'])->name('web3.store');
        Route::get('/web3/{web3Wallet}/edit', [Web3WalletController::class, 'edit'])->name('web3.edit');
        Route::put('/web3/{web3Wallet}', [Web3WalletController::class, 'update'])->name('web3.update');
        Route::delete('/web3/{web3Wallet}', [Web3WalletController::class, 'destroy'])->name('web3.destroy');
        Route::get('/web3/{web3Wallet}/details', [Web3WalletController::class, 'details'])->name('web3.details');
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

Route::middleware(['auth'])->group(function () {
    // Existing routes...

    // Onboarding API routes
    Route::prefix('api/onboarding')->name('api.onboarding.')->group(function () {
        Route::post('/complete', [OnboardingController::class, 'complete'])->name('complete');

        // Only available in non-production environments
        if (!app()->environment('production')) {
            Route::post('/reset', [OnboardingController::class, 'reset'])->name('reset');
        }
    });
});