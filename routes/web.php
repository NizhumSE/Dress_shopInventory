<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

require __DIR__ . '/auth.php';

// Authentication
Auth::routes();

// Admin Routes
Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Admin\ProductController::class, 'dashboard']);
    Route::resource('products', App\Http\Controllers\Admin\ProductController::class);
    Route::resource('barcodes', App\Http\Controllers\Admin\BarcodeController::class);
});

// User Routes
Route::middleware(['auth', 'user'])->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\User\ScanController::class, 'dashboard']);
    Route::resource('scan', App\Http\Controllers\User\ScanController::class);
    Route::resource('checkout', App\Http\Controllers\User\CheckoutController::class);
});
