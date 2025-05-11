<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Auth;

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

        // Total products
        $totalProducts = Product::where('user_id', $sellerId)->count();

        // Total orders and pending payments
        $orders = Order::whereHas('items.product', function ($query) use ($sellerId) {
            $query->where('user_id', $sellerId);
        })->get();
        $totalOrders = $orders->count();
        $pendingPayments = $orders->where('status', 'pending')->count();

        // Total revenue
        $totalRevenue = $orders->where('status', 'completed')->sum('total_price');

        // Low stock products
        $lowStockProducts = Product::where('user_id', $sellerId)
            ->where('stock_quantity', '<=', 5)
            ->count();

        // Recent orders
        $recentOrders = Order::whereHas('items.product', function ($query) use ($sellerId) {
            $query->where('user_id', $sellerId);
        })
            ->latest()
            ->take(5)
            ->get();

        // Seller's products
        $products = Product::where('user_id', $sellerId)
            ->with('primaryImage')
            ->get();

        // Top selling products
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

        // Orders over time (e.g., last 30 days)
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

        return view('seller.dashboard', compact(
            'totalProducts',
            'totalOrders',
            'pendingPayments',
            'totalRevenue',
            'lowStockProducts',
            'recentOrders',
            'products',
            'topSellingProducts',
            'topSellingProductNames',
            'orderLabels',
            'orderValues'
        ));
    }
}