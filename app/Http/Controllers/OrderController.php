<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'admin'])->except(['apiUserOrders']);
        $this->middleware('auth:sanctum')->only(['apiUserOrders']);
    }

    // Web Routes
    public function index()
    {
        Log::info('OrderController@index called', [
            'user_id' => Auth::id(),
            'email' => Auth::user()->email,
            'session_id' => session()->getId(),
            'is_admin' => Auth::user()->is_admin,
        ]);
        $orders = Order::with(['user', 'orderItems.product', 'payments'])->get();
        Log::info('OrderController: Displaying orders index', [
            'count' => $orders->count(),
            'user_id' => Auth::id(),
            'session_id' => session()->getId(),
        ]);
        return view('admin.orders.index', compact('orders'));
    }

    public function create()
    {
        $users = User::all();
        $products = Product::all();
        return view('admin.orders.create', compact('users', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'status' => 'required|in:pending,shipped,completed,cancelled',
        ]);

        try {
            DB::beginTransaction();

            $order = Order::create([
                'user_id' => $request->user_id,
                'total_price' => 0, // Will be calculated
                'status' => $request->status,
            ]);

            $totalPrice = 0;
            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $itemPrice = $product->price * $item['quantity'];
                $totalPrice += $itemPrice;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                ]);
            }

            $order->update(['total_price' => $totalPrice]);

            DB::commit();
            Log::info('Order created', [
                'order_id' => $order->id,
                'user_id' => Auth::id(),
                'email' => Auth::user()->email,
            ]);

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Order created successfully', 'order' => $order->load('orderItems')], 201);
            }
            return redirect()->route('orders.index')->with('success', 'Order created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating order: ' . $e->getMessage());
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Failed to create order'], 500);
            }
            return back()->withErrors(['error' => 'Failed to create order.']);
        }
    }

    public function show(Order $order)
    {
        $order->load(['user', 'orderItems.product', 'payments', 'downloads']);
        return view('admin.orders.show', compact('order'));
    }

    public function edit(Order $order)
    {
        $users = User::all();
        $products = Product::all();
        $order->load('orderItems');
        return view('admin.orders.edit', compact('order', 'users', 'products'));
    }

    public function update(Request $request, Order $order)
    {
        $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'status' => 'required|in:pending,shipped,completed,cancelled',
        ]);

        try {
            DB::beginTransaction();

            $order->orderItems()->delete(); // Remove existing items
            $totalPrice = 0;
            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $itemPrice = $product->price * $item['quantity'];
                $totalPrice += $itemPrice;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                ]);
            }

            $order->update([
                'user_id' => $request->user_id,
                'total_price' => $totalPrice,
                'status' => $request->status,
            ]);

            DB::commit();
            Log::info('Order updated', [
                'order_id' => $order->id,
                'user_id' => Auth::id(),
                'email' => Auth::user()->email,
            ]);

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Order updated successfully', 'order' => $order->load('orderItems')], 200);
            }
            return redirect()->route('orders.index')->with('success', 'Order updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating order: ' . $e->getMessage());
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Failed to update order'], 500);
            }
            return back()->withErrors(['error' => 'Failed to update order.']);
        }
    }

    public function destroy(Order $order)
    {
        try {
            $order->delete(); // Cascades to order_items, payments, downloads
            Log::info('Order deleted', [
                'order_id' => $order->id,
                'user_id' => Auth::id(),
                'email' => Auth::user()->email,
            ]);

            if (request()->expectsJson()) {
                return response()->json(['message' => 'Order deleted successfully'], 200);
            }
            return redirect()->route('orders.index')->with('success', 'Order deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting order: ' . $e->getMessage());
            if (request()->expectsJson()) {
                return response()->json(['error' => 'Failed to delete order'], 500);
            }
            return back()->withErrors(['error' => 'Failed to delete order.']);
        }
    }

    // API Routes
    public function apiIndex(Request $request)
    {
        $orders = Order::with(['user', 'orderItems.product', 'payments', 'downloads'])->get();
        return response()->json($orders, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    public function apiShow(Order $order)
    {
        $order->load(['user', 'orderItems.product', 'payments', 'downloads']);
        return response()->json($order, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    public function apiStore(Request $request)
    {
        $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'status' => 'required|in:pending,shipped,completed,cancelled',
        ]);

        try {
            DB::beginTransaction();

            $order = Order::create([
                'user_id' => $request->user_id,
                'total_price' => 0,
                'status' => $request->status,
            ]);

            $totalPrice = 0;
            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $itemPrice = $product->price * $item['quantity'];
                $totalPrice += $itemPrice;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                ]);
            }

            $order->update(['total_price' => $totalPrice]);

            DB::commit();
            return response()->json(['message' => 'Order created successfully', 'order' => $order->load('orderItems')], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating order: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to create order'], 500);
        }
    }

    public function apiUpdate(Request $request, Order $order)
    {
        $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'status' => 'required|in:pending,shipped,completed,cancelled',
        ]);

        try {
            DB::beginTransaction();

            $order->orderItems()->delete();
            $totalPrice = 0;
            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $itemPrice = $product->price * $item['quantity'];
                $totalPrice += $itemPrice;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                ]);
            }

            $order->update([
                'user_id' => $request->user_id,
                'total_price' => $totalPrice,
                'status' => $request->status,
            ]);

            DB::commit();
            return response()->json(['message' => 'Order updated successfully', 'order' => $order->load('orderItems')], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating order: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update order'], 500);
        }
    }

    public function apiDestroy(Order $order)
    {
        try {
            $order->delete();
            return response()->json(['message' => 'Order deleted successfully'], 200);
        } catch (\Exception $e) {
            Log::error('Error deleting order: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete order'], 500);
        }
    }

    public function apiUserOrders(Request $request)
    {
        $orders = Order::where('user_id', Auth::id())
            ->with(['orderItems.product', 'payments', 'downloads'])
            ->get();
        return response()->json($orders, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}