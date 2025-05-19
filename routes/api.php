<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\APIAuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These routes
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

// Unauthenticated Categories Route
Route::get('/categories', [CategoryController::class, 'index']);

/** ------------------------------------------------------------------
 *  Protected routes (auth:sanctum)
 *  ------------------------------------------------------------------
 */
Route::middleware('auth:sanctum')->group(function () {
    Log::info('Sanctum middleware processing', [
        'user_authenticated' => auth('sanctum')->check() ? 'yes' : 'no',
        'user_id' => auth('sanctum')->check() ? auth('sanctum')->id() : 'none',
        'path' => request()->path(),
        'session_id' => session()->getId(),
    ]);

    /* --- Auth --- */
    Route::get('profile', [APIAuthController::class, 'profile']);
    Route::post('logout', [APIAuthController::class, 'logout']);
    Route::get('users', [APIAuthController::class, 'apiUsers']);

    /* --- Product routes --- */
    Route::get('/products', [ProductController::class, 'apiIndex']);
    Route::get('/products/{product}', [ProductController::class, 'apiShow']);
    Route::post('/products', [ProductController::class, 'apiStore']);
    Route::put('/products/{product}', [ProductController::class, 'apiUpdate']);
    Route::delete('/products/{product}', [ProductController::class, 'apiDestroy']);
    Route::get('/categories', [ProductController::class, 'apiCategories']);

    /* --- Order routes --- */
    Route::get('/orders', [OrderController::class, 'apiIndex']);
    Route::get('/orders/{order}', [OrderController::class, 'apiShow']);
    Route::post('/orders', [OrderController::class, 'apiStore']);
    Route::put('/orders/{order}', [OrderController::class, 'apiUpdate']);
    Route::delete('/orders/{order}', [ProductController::class, 'apiDestroy']);
    Route::get('/user/orders', [OrderController::class, 'apiUserOrders']);

    /* --- Category routes --- */
    Route::apiResource('categories', CategoryController::class)->except(['index', 'create', 'edit']);

    /* --- PayPal checkout --- */
    Route::post('checkout', [CheckoutController::class, 'checkout']);
    Route::get('order/paypal/success', [CheckoutController::class, 'success'])->name('order.paypal.success');
    Route::get('order/paypal/cancel', [CheckoutController::class, 'cancel'])->name('order.paypal.cancel');

    /* --- Cart --- */
    Route::prefix('cart')->group(function () {
        Route::post('add', [CartController::class, 'addToCart']);
        Route::post('remove', [CartController::class, 'removeFromCart']);
        Route::get('view', [CartController::class, 'viewCart']);
        Route::get('count', [CartController::class, 'getCartCount']);
        Route::post('update-order-status', [CartController::class, 'updateOrderStatus']);
    });

    /* --- Pay Later checkout --- */
    Route::post('checkout/paylater', [CheckoutController::class, 'payLater']);
});

Log::info('routes/api.php finished loading at ' . now()->toDateTimeString());