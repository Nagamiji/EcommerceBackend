<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Order;
use App\Models\User;
use App\Models\Cart;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function dashboard()
    {
        $totalProducts = Product::count();
        $totalCategories = Category::count();
        $totalOrders = Order::count();
        $pendingPayments = Order::where('status', 'pending')->count();
        $totalRevenue = Order::where('status', 'completed')->sum('total_price');
        $totalUsers = User::count();
        $lowStockProducts = Product::where('stock_quantity', '<', 10)->count();
        $customers = User::where('role', 'customer')->count();
        $sellers = User::where('role', 'seller')->count();
        $admins = User::where('role', 'admin')->orWhere('is_admin', true)->count();
        $newUsersToday = User::whereDate('created_at', today())->count();
        $activeCarts = Cart::whereNotNull('user_id')->count();
        $recentOrders = Order::with('user')->latest()->take(5)->get();
        $topSellingProducts = OrderItem::with('product')
            ->select('product_id', DB::raw('SUM(quantity) as quantity'))
            ->groupBy('product_id')
            ->orderByDesc('quantity')
            ->take(5)
            ->get();
        $categoriesWithProducts = Category::withCount('products')->get();
        $orderData = Order::select(DB::raw("DATE(created_at) as date"), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->take(30)
            ->get();
        $orderLabels = $orderData->pluck('date');
        $orderValues = $orderData->pluck('count');

        Log::info('Admin dashboard accessed', [
            'user_id' => Auth::id(),
            'email' => Auth::user()->email,
            'session_id' => session()->getId(),
            'role' => Auth::user()->role,
        ]);

        return view('admin.dashboard', compact(
            'totalProducts',
            'totalCategories',
            'totalOrders',
            'pendingPayments',
            'totalRevenue',
            'totalUsers',
            'lowStockProducts',
            'customers',
            'sellers',
            'admins',
            'newUsersToday',
            'activeCarts',
            'recentOrders',
            'topSellingProducts',
            'categoriesWithProducts',
            'orderLabels',
            'orderValues'
        ));
    }
}