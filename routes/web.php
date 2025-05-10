<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\AuthController;

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::get('/logout', function () {
    return view('auth.logout');
})->name('logout');

Route::middleware(['jwt.auth', 'admin'])->group(function () {
    Route::get('/admin/dashboard', [App\Http\Controllers\AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/admin/products', [App\Http\Controllers\ProductController::class, 'indexAdmin'])->name('admin.products');
    Route::get('/admin/categories', [App\Http\Controllers\CategoryController::class, 'indexAdmin'])->name('admin.categories');
    Route::get('/admin/orders', [App\Http\Controllers\OrderController::class, 'indexAdmin'])->name('admin.orders');
});