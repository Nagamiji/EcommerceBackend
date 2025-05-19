<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SellerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('seller');
    }

    public function dashboard()
    {
        $sellerId = Auth::id();
        Log::info('Seller dashboard: Starting', ['seller_id' => $sellerId]);

        // Total products
        Log::info('Seller dashboard: Fetching total products');
        $totalProducts = Product::where('user_id', $sellerId)->count();
        Log::info('Seller dashboard: Total products fetched', ['count' => $totalProducts]);

        // Total orders and pending payments
        Log::info('Seller dashboard: Fetching orders');
        try {
            $orders = Order::whereHas('items.product', function ($query) use ($sellerId) {
                $query->where('user_id', $sellerId);
            })->get();
            $totalOrders = $orders->count();
            $pendingPayments = $orders->where('status', 'pending')->count();
            Log::info('Seller dashboard: Orders fetched', ['total' => $totalOrders, 'pending' => $pendingPayments]);
        } catch (\Exception $e) {
            Log::error('Seller dashboard: Order query failed', ['error' => $e->getMessage()]);
            throw $e;
        }

        // Total revenue
        Log::info('Seller dashboard: Calculating revenue');
        $totalRevenue = $orders->where('status', 'completed')->sum('total_price');
        Log::info('Seller dashboard: Revenue calculated', ['revenue' => $totalRevenue]);

        // Low stock products
        Log::info('Seller dashboard: Fetching low stock products');
        $lowStockProducts = Product::where('user_id', $sellerId)
            ->where('stock_quantity', '<=', 5)
            ->count();
        Log::info('Seller dashboard: Low stock products fetched', ['count' => $lowStockProducts]);

        // Recent orders
        Log::info('Seller dashboard: Fetching recent orders');
        $recentOrders = Order::whereHas('items.product', function ($query) use ($sellerId) {
            $query->where('user_id', $sellerId);
        })
            ->latest()
            ->take(5)
            ->get();
        Log::info('Seller dashboard: Recent orders fetched', ['count' => $recentOrders->count()]);

        // Seller's products
        Log::info('Seller dashboard: Fetching products with primary image');
        $products = Product::where('user_id', $sellerId)
            ->with('primaryImage')
            ->get();
        Log::info('Seller dashboard: Products fetched', ['count' => $products->count()]);

        // Top selling products
        Log::info('Seller dashboard: Fetching top selling products');
        $topSellingProducts = OrderItem::selectRaw('product_id, SUM(quantity) as quantity')
            ->whereHas('product', function ($query) use ($sellerId) {
                $query->where('user_id', $sellerId);
            })
            ->groupBy('product_id')
            ->orderByDesc('quantity')
            ->take(10)
            ->get();
        $topSellingProductNames = $topSellingProducts->map(function ($item) {
            return $item->product ? $item->product->name : 'Product #' . $item->product_id;
        });
        Log::info('Seller dashboard: Top selling products fetched', ['count' => $topSellingProducts->count()]);

        // Orders over time
        Log::info('Seller dashboard: Fetching orders over time');
        $orderData = Order::whereHas('items.product', function ($query) use ($sellerId) {
            $query->where('user_id', $sellerId);
        })
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
        $orderLabels = $orderData->pluck('date');
        $orderValues = $orderData->pluck('count');
        Log::info('Seller dashboard: Orders over time fetched', ['labels' => $orderLabels]);

        if (request()->expectsJson()) {
            Log::info('Seller dashboard: Returning JSON response');
            return response()->json(compact(
                'totalProducts', 'totalOrders', 'pendingPayments', 'totalRevenue',
                'lowStockProducts', 'recentOrders', 'products', 'topSellingProducts',
                'topSellingProductNames', 'orderLabels', 'orderValues'
            ));
        }

        Log::info('Seller dashboard: Rendering view');
        return view('seller.dashboard', compact(
            'totalProducts', 'totalOrders', 'pendingPayments', 'totalRevenue',
            'lowStockProducts', 'recentOrders', 'products', 'topSellingProducts',
            'topSellingProductNames', 'orderLabels', 'orderValues'
        ));
    }
}