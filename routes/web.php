<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\SellerController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/home', [HomeController::class, 'index'])->name('home')->middleware('auth');

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



    // Add seller actions under admin
    Route::get('/seller/dashboard', [SellerController::class, 'dashboard'])->name('seller.dashboard');
    Route::get('/seller/products/create', [SellerController::class, 'createProduct'])->name('seller.products.create');
    Route::post('/seller/products', [SellerController::class, 'storeProduct'])->name('seller.products.store');
    Route::get('/seller/products/{product}/edit', [SellerController::class, 'editProduct'])->name('seller.products.edit');
    Route::put('/seller/products/{product}', [SellerController::class, 'updateProduct'])->name('seller.products.update');
    Route::delete('/seller/products/{product}', [SellerController::class, 'destroyProduct'])->name('seller.products.destroy');
});

// Route::prefix('seller')->group(function () {
//     Route::get('/dashboard', [SellerController::class, 'dashboard'])
//         ->name('seller.dashboard')
//         ->middleware(['auth', \App\Http\Middleware\Seller::class]);
//     Route::get('/products/create', [SellerController::class, 'createProduct'])
//         ->name('seller.products.create')
//         ->middleware(['auth', \App\Http\Middleware\Seller::class]);
//     Route::post('/products', [SellerController::class, 'storeProduct'])
//         ->name('seller.products.store')
//         ->middleware(['auth', \App\Http\Middleware\Seller::class]);
//     Route::get('/products/{product}/edit', [SellerController::class, 'editProduct'])
//         ->name('seller.products.edit')
//         ->middleware(['auth', \App\Http\Middleware\Seller::class]);
//     Route::put('/products/{product}', [SellerController::class, 'updateProduct'])
//         ->name('seller.products.update')
//         ->middleware(['auth', \App\Http\Middleware\Seller::class]);
//     Route::delete('/products/{product}', [SellerController::class, 'destroyProduct'])
//         ->name('seller.products.destroy')
//         ->middleware(['auth', \App\Http\Middleware\Seller::class]);
// });


Route::get('/test-seller', function () {
    try {
        $middleware = app(\App\Http\Middleware\Seller::class);
        return $middleware->handle(request(), fn($req) => response('Seller middleware passed'));
    } catch (\Exception $e) {
        \Log::error('Middleware test failed: ' . $e->getMessage());
        return response('Error: ' . $e->getMessage(), 500);
    }
});

Route::get('/test-seller-direct', function () {
    try {
        $middleware = new \App\Http\Middleware\Seller();
        return $middleware->handle(request(), fn($req) => response('Seller middleware instantiated directly'));
    } catch (\Exception $e) {
        \Log::error('Direct middleware instantiation failed: ' . $e->getMessage());
        return response('Error: ' . $e->getMessage(), 500);
    }
});