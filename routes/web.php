<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\SellerController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home')->middleware('auth');

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/logout', function () {
    return redirect()->route('login')->with('error', 'Please use the logout button in the navbar.');
})->name('logout.get');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::post('/register-seller', [AdminController::class, 'registerSeller'])->name('admin.register-seller');
    Route::resource('products', ProductController::class);
    Route::resource('categories', CategoryController::class);
    Route::resource('orders', OrderController::class);
});

Route::prefix('seller')->middleware(['auth', 'seller'])->group(function () {
    Route::get('/dashboard', [SellerController::class, 'dashboard'])->name('seller.dashboard');
    Route::get('/products/create', [SellerController::class, 'createProduct'])->name('seller.products.create');
    Route::post('/products', [SellerController::class, 'storeProduct'])->name('seller.products.store');
    Route::get('/products/{product}/edit', [SellerController::class, 'editProduct'])->name('seller.products.edit');
    Route::put('/products/{product}', [SellerController::class, 'updateProduct'])->name('seller.products.update');
    Route::delete('/products/{product}', [SellerController::class, 'destroyProduct'])->name('seller.products.destroy');
});

Route::middleware(['auth', 'seller'])->group(function () {
    Route::get('/seller/dashboard', [App\Http\Controllers\SellerController::class, 'dashboard'])->name('seller.dashboard');
    // Add other seller routes here if needed
});