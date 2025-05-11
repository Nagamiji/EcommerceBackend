<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    public function dashboard()
    {
        Log::info('Dashboard accessed by user: ' . (auth()->check() ? auth()->user()->email : 'unauthenticated'));

        if (!auth()->check() || !auth()->user()->isAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $totalProducts = Product::count();
        $totalCategories = Category::count();
        $totalOrders = Order::count();
        $totalRevenue = Order::sum('total_price');
        $pendingPayments = Order::where('status', 'pending')->count();

        $topSellers = \DB::table('products')
            ->select('user_id', \DB::raw('count(*) as product_count'), \DB::raw('sum(price * stock_quantity) as revenue'))
            ->groupBy('user_id')
            ->orderBy('revenue', 'desc')
            ->take(5)
            ->get()
            ->map(function ($item) {
                $user = User::find($item->user_id);
                return (object) ['user' => $user, 'product_count' => $item->product_count, 'revenue' => $item->revenue];
            });

        $topSellingProducts = \DB::table('order_items')
            ->select('product_id', \DB::raw('sum(quantity) as quantity'))
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->groupBy('product_id')
            ->orderBy('quantity', 'desc')
            ->take(5)
            ->get()
            ->map(function ($item) {
                $product = Product::find($item->product_id);
                return (object) ['product' => $product, 'quantity' => $item->quantity];
            });

        $orderData = Order::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->where('created_at', '>=', Carbon::now()->subDays(5))
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date')
            ->all();
        $orderLabels = array_keys($orderData);
        $orderValues = array_values($orderData);

        $totalUsers = User::count();
        $customers = User::where('role', 'customer')->count();
        $sellers = User::where('role', 'seller')->count();
        $admins = User::where('is_admin', 1)->count();
        $newUsersToday = User::whereDate('created_at', Carbon::today())->count();
        $activeCarts = 0; // Implement cart logic if needed
        $lowStockProducts = Product::where('stock_quantity', '<', 10)->count();

        $recentOrders = Order::with('user')->latest()->take(5)->get();
        $categoriesWithProducts = Category::withCount('products')->orderBy('products_count', 'desc')->take(5)->get();

        return view('admin.dashboard', compact(
            'totalProducts', 'totalCategories', 'totalOrders', 'totalRevenue',
            'pendingPayments', 'topSellers', 'topSellingProducts', 'orderLabels',
            'orderValues', 'totalUsers', 'customers', 'sellers', 'admins',
            'newUsersToday', 'activeCarts', 'lowStockProducts', 'recentOrders',
            'categoriesWithProducts'
        ));
    }

    public function registerSeller(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => 'seller',
            'is_admin' => false,
        ]);

        return redirect()->back()->with('success', 'Seller registered successfully.');
    }
}