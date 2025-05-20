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
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::post('/register-seller', [AdminController::class, 'registerSeller'])->name('admin.register-seller');
    Route::resource('products', ProductController::class)->names([
        'index' => 'products.index',
        'create' => 'products.create',
        'store' => 'products.store',
        'show' => 'products.show',
        'edit' => 'products.edit',
        'update' => 'products.update',
        'destroy' => 'products.destroy',
    ]);
    Route::resource('orders', OrderController::class);
    Route::resource('categories', CategoryController::class)->except(['show']);
    Route::get('/categories/{category}', [CategoryController::class, 'showWeb'])->name('categories.show');
});

Route::prefix('seller')->middleware(['auth', 'seller'])->group(function () {
    Route::get('/dashboard', [SellerController::class, 'dashboard'])->name('seller.dashboard');
    Route::get('/products/create', [SellerController::class, 'createProduct'])->name('seller.products.create');
    Route::post('/products', [SellerController::class, 'storeProduct'])->name('seller.products.store');
    Route::get('/products/{product}/edit', [SellerController::class, 'editProduct'])->name('seller.products.edit');
    Route::put('/products/{product}', [SellerController::class, 'updateProduct'])->name('seller.products.update');
    Route::delete('/products/{product}', [SellerController::class, 'destroyProduct'])->name('seller.products.destroy');
});

Route::prefix('api')->group(function () {
    Route::middleware('api.public')->group(function () {
        Route::get('/products', [ProductController::class, 'publicIndex']);
        Route::get('/categories', [CategoryController::class, 'publicIndex']);
    });

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/orders', [OrderController::class, 'index']);
    });
});

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