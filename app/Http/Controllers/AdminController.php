<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        \Log::info('Dashboard accessed by user: ' . (auth()->check() ? auth()->user()->email : 'unauthenticated'));

        if (!auth()->check() || !auth()->user()->is_admin) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $totalProducts = Product::count();
        $totalCategories = Category::count();
        $totalOrders = Order::count();
        $totalRevenue = Order::sum('total_price');
        $pendingPayments = Order::where('status', 'pending')->count();

        $totalUsers = User::count();
        $customers = User::where('role', 'customer')->count();
        $sellers = User::where('role', 'seller')->count();
        $admins = User::where('is_admin', 1)->count();
        $newUsersToday = User::whereDate('created_at', Carbon::today())->count();
        $activeCarts = 0; // Implement cart logic if needed

        $lowStockProducts = Product::where('stock_quantity', '<', 10)->count();

        $recentOrders = Order::with('user')->latest()->take(5)->get();
        $topSellingProducts = \DB::table('order_items')
            ->select('product_id', \DB::raw('sum(quantity) as quantity'))
            ->groupBy('product_id')
            ->orderBy('quantity', 'desc')
            ->take(5)
            ->get();

        $categoriesWithProducts = Category::withCount('products')->orderBy('products_count', 'desc')->take(5)->get();

        $orderData = Order::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->where('created_at', '>=', Carbon::now()->subDays(4))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date')
            ->all();
        $orderLabels = $orderData ? array_keys($orderData) : [];
        $orderValues = $orderData ? array_values($orderData) : [];

        return view('admin.dashboard', compact(
            'totalProducts', 'totalCategories', 'totalOrders', 'totalRevenue',
            'pendingPayments', 'totalUsers', 'customers', 'sellers', 'admins',
            'newUsersToday', 'activeCarts', 'lowStockProducts', 'recentOrders',
            'topSellingProducts', 'categoriesWithProducts', 'orderLabels', 'orderValues'
        ));
    }
}