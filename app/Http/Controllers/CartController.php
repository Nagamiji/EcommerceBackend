<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CartController extends Controller
{
    public function addToCart(Request $request)
    {
        try {
            $user = auth()->user();
            $productId = $request->input('product_id');
            $quantity = $request->input('quantity', 1);

            if (!$productId || $quantity <= 0) {
                Log::warning('Invalid cart add request', [
                    'user_id' => $user->id,
                    'product_id' => $productId,
                    'quantity' => $quantity
                ]);
                return response()->json(['error' => 'Invalid product or quantity'], 400);
            }

            $cartItem = Cart::where('user_id', $user->id)
                           ->where('product_id', $productId)
                           ->first();

            if ($cartItem) {
                $cartItem->quantity += $quantity;
                $cartItem->save();
            } else {
                $cartItem = Cart::create([
                    'user_id' => $user->id,
                    'product_id' => $productId,
                    'quantity' => $quantity,
                ]);
            }

            Log::info('Product added to cart', [
                'user_id' => $user->id,
                'product_id' => $productId,
                'quantity' => $quantity
            ]);

            return response()->json([
                'message' => 'Product added to cart successfully',
                'cart_item' => $cartItem
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to add product to cart: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Failed to add product to cart'], 500);
        }
    }

    public function removeFromCart(Request $request)
    {
        try {
            $user = auth()->user();
            $productId = $request->input('product_id');

            if (!$productId) {
                Log::warning('Invalid cart remove request', [
                    'user_id' => $user->id,
                    'product_id' => $productId
                ]);
                return response()->json(['error' => 'Invalid product ID'], 400);
            }

            $cartItem = Cart::where('user_id', $user->id)
                           ->where('product_id', $productId)
                           ->first();

            if (!$cartItem) {
                Log::warning('Product not found in cart', [
                    'user_id' => $user->id,
                    'product_id' => $productId
                ]);
                return response()->json(['error' => 'Product not found in cart'], 404);
            }

            $cartItem->delete();

            Log::info('Product removed from cart', [
                'user_id' => $user->id,
                'product_id' => $productId
            ]);

            return response()->json(['message' => 'Product removed from cart successfully'], 200);
        } catch (\Exception $e) {
            Log::error('Failed to remove product from cart: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Failed to remove product from cart'], 500);
        }
    }

    public function viewCart(Request $request)
    {
        try {
            $user = auth()->user();
            $cartItems = Cart::where('user_id', $user->id)
                            ->with('product')
                            ->get();

            $cartItems = $cartItems->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'total_price' => $item->quantity * $item->product->price,
                    'product' => [
                        'product_name' => $item->product->name,
                        'priceUSD' => $item->product->price,
                        'image' => $item->product->image_url ?? 'storage/placeholder.jpg',
                    ],
                ];
            });

            Log::info('Cart viewed', [
                'user_id' => $user->id,
                'cart_items_count' => $cartItems->count()
            ]);

            return response()->json(['data' => $cartItems], 200);
        } catch (\Exception $e) {
            Log::error('Failed to view cart: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Failed to view cart'], 500);
        }
    }

    public function getCartCount(Request $request)
    {
        try {
            $user = auth()->user();
            $count = Cart::where('user_id', $user->id)->count();

            Log::info('Cart count retrieved', [
                'user_id' => $user->id,
                'count' => $count
            ]);

            return response()->json(['count' => $count], 200);
        } catch (\Exception $e) {
            Log::error('Failed to get cart count: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Failed to get cart count'], 500);
        }
    }

    public function updateOrderStatus(Request $request)
    {
        try {
            $user = auth()->user();
            $orderId = $request->input('order_id');
            $status = $request->input('status');

            if (!$orderId || !$status) {
                Log::warning('Invalid order status update request', [
                    'user_id' => $user->id,
                    'order_id' => $orderId,
                    'status' => $status
                ]);
                return response()->json(['error' => 'Invalid order ID or status'], 400);
            }

            $order = Order::where('id', $orderId)
                         ->where('user_id', $user->id)
                         ->first();

            if (!$order) {
                Log::warning('Order not found for status update', [
                    'user_id' => $user->id,
                    'order_id' => $orderId
                ]);
                return response()->json(['error' => 'Order not found'], 404);
            }

            $order->status = $status;
            $order->save();

            Log::info('Order status updated', [
                'user_id' => $user->id,
                'order_id' => $orderId,
                'new_status' => $status
            ]);

            return response()->json(['message' => 'Order status updated successfully'], 200);
        } catch (\Exception $e) {
            Log::error('Failed to update order status: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Failed to update order status'], 500);
        }
    }
}