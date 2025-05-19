<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\APIAuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\SellerController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\PaymentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you register API routes for your application. These routes
| are loaded by the RouteServiceProvider within a group assigned the 
| "api" middleware group. Enjoy building your API!
|
*/

Log::info('routes/api.php loading … ' . now()->toDateTimeString());

/** ------------------------------------------------------------------
 *  Public routes (no auth required)
 *  ------------------------------------------------------------------
 */
Route::post('register', [APIAuthController::class, 'register']);
Route::post('login', [APIAuthController::class, 'login']);
Route::post('register-seller', [APIAuthController::class, 'registerSeller']);

Route::prefix('public')->group(function () {
    Route::get('products', [ProductController::class, 'publicIndex']);
    Route::get('products/{id}', [ProductController::class, 'showPublic']);
    Route::get('categories', [CategoryController::class, 'publicIndex']);
});

Route::get('search-products', [ProductController::class, 'searchProducts']);

/** ------------------------------------------------------------------
 *  Protected routes (auth:sanctum)
 *  ------------------------------------------------------------------
 */
Route::middleware('auth:sanctum')->group(function () {
    /* --- Auth --- */
    Route::get('profile', [APIAuthController::class, 'profile']);
    Route::post('logout', [APIAuthController::class, 'logout']);

    /* --- CRUD resources --- */
    Route::apiResource('products', ProductController::class)->except(['create', 'edit']);
    Route::apiResource('categories', CategoryController::class)->except(['create', 'edit']);
    Route::apiResource('orders', OrderController::class)->except(['create', 'edit']);

    /* --- PayPal checkout --- */
    Route::post('checkout', [CheckoutController::class, 'checkout']);
    Route::get('order/paypal/success', [CheckoutController::class, 'success'])->name('order.paypal.success');
    Route::get('order/paypal/cancel', [CheckoutController::class, 'cancel'])->name('order.paypal.cancel');

    /* --- Seller-only actions --- */
    Route::prefix('seller')->name('api.seller.')->group(function () {
        Route::get('dashboard', [SellerController::class, 'dashboard']);
        Route::get('products/create', [SellerController::class, 'createProduct']);
        Route::post('products', [SellerController::class, 'storeProduct']);
        Route::get('products/{product}/edit', [SellerController::class, 'editProduct']);
        Route::put('products/{product}', [SellerController::class, 'updateProduct']);
        Route::delete('products/{product}', [SellerController::class, 'destroyProduct']);
    });

    /* --- Cart --- */
    Route::prefix('cart')->group(function () {
        Route::post('add', [CartController::class, 'addToCart']);
        Route::post('remove', [CartController::class, 'removeFromCart']);
        Route::get('view', [CartController::class, 'viewCart']);
        Route::get('count', [CartController::class, 'getCartCount']);
        Route::post('update-order-status', [CartController::class, 'updateOrderStatus']);
    });

    /* --- New Pay Later checkout --- */
    Route::post('checkout/paylater', [CheckoutController::class, 'payLater']);
});

Log::info('routes/api.php finished loading at ' . now()->toDateTimeString());