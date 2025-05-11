<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin')->only(['index', 'show', 'destroy']);
        $this->middleware(function ($request, $next) {
            if (Auth::check() && (Auth::user()->is_admin || Auth::user()->role === 'seller')) {
                return $next($request);
            }
            return redirect()->route('home')->with('error', 'Unauthorized access');
        })->only(['create', 'store', 'edit', 'update']);
    }

    public function index()
    {
        $products = Product::with(['images', 'category', 'user'])->get();
        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::all();
        $view = Auth::user()->is_admin ? 'admin.products.create' : 'seller.products.create';
        return view($view, compact('categories'));
    }

    public function store(Request $request)
    {
        Log::info('Store method called', ['request_data' => $request->all()]);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'is_public' => 'required|boolean',
            'image_url' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            Log::info('Validation passed');

            $imageUrl = null;
            if ($request->hasFile('image_url')) {
                Log::info('Primary image uploaded: ' . $request->file('image_url')->getClientOriginalName());
                $imageUrl = $request->file('image_url')->store('products', 'public');
                Log::info('Primary image stored at: ' . $imageUrl);
            } else {
                Log::info('No primary image uploaded.');
            }

            $product = Product::create([
                'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price,
                'stock_quantity' => $request->stock_quantity,
                'category_id' => $request->category_id,
                'is_public' => $request->is_public,
                'image_url' => $imageUrl,
                'user_id' => auth()->id(),
            ]);
            Log::info('Product created', ['product_id' => $product->id]);

            if ($imageUrl) {
                $product->images()->create([
                    'image_url' => $imageUrl,
                    'is_primary' => true,
                ]);
                Log::info('Primary image saved to product_images');
            }

            if ($request->hasFile('images')) {
                Log::info('Additional images uploaded: ' . count($request->file('images')));
                foreach ($request->file('images') as $index => $image) {
                    $path = $image->store('product_images', 'public');
                    $product->images()->create([
                        'image_url' => $path,
                        'is_primary' => !$imageUrl && $index === 0,
                    ]);
                    Log::info('Additional image saved: ' . $path);
                }
            } else {
                Log::info('No additional images uploaded.');
            }

            $role = Auth::user()->is_admin ? 'admin' : 'seller';
            Log::info("Product created by $role: " . auth()->user()->email . ', Product ID: ' . $product->id);

            if (Auth::user()->is_admin) {
                return redirect()->route('products.index')->with('success', 'Product created successfully.');
            }
            return redirect()->route('seller.dashboard')->with('success', 'Product created successfully.');
        } catch (\Exception $e) {
            Log::error('Error creating product: ' . $e->getMessage(), ['stack' => $e->getTraceAsString()]);
            return back()->withErrors(['error' => 'Failed to create product.']);
        }
    }

    public function show(Product $product)
    {
        $product->load(['images', 'category', 'user']);
        return view('admin.products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        if ($product->user_id !== auth()->id()) {
            return redirect()->route('seller.dashboard')->with('error', 'Unauthorized action.');
        }

        $categories = Category::all();
        $view = Auth::user()->is_admin ? 'admin.products.edit' : 'seller.products.edit';
        return view($view, compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        if ($product->user_id !== auth()->id()) {
            return redirect()->route('seller.dashboard')->with('error', 'Unauthorized action.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'is_public' => 'required|boolean',
            'image_url' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            $newImageUrl = null;
            if ($request->hasFile('image_url')) {
                if ($product->image_url) {
                    Storage::disk('public')->delete($product->image_url);
                }
                $newImageUrl = $request->file('image_url')->store('products', 'public');
                $product->image_url = $newImageUrl;
            }

            $product->update([
                'name' => $request->name,
                'description' => $request->description,
                'price' => $request->price,
                'stock_quantity' => $request->stock_quantity,
                'category_id' => $request->category_id,
                'is_public' => $request->is_public,
            ]);

            if ($newImageUrl) {
                $product->images()->where('is_primary', true)->delete();
                $product->images()->create([
                    'image_url' => $newImageUrl,
                    'is_primary' => true,
                ]);
            }

            if ($request->hasFile('images')) {
                foreach ($product->images as $image) {
                    Storage::disk('public')->delete($image->image_url);
                    $image->delete();
                }
                foreach ($request->file('images') as $index => $image) {
                    $path = $image->store('product_images', 'public');
                    $product->images()->create([
                        'image_url' => $path,
                        'is_primary' => !$product->image_url && $index === 0,
                    ]);
                }
            }

            $role = Auth::user()->is_admin ? 'admin' : 'seller';
            Log::info("Product updated by $role: " . auth()->user()->email . ', Product ID: ' . $product->id);

            if (Auth::user()->is_admin) {
                return redirect()->route('products.index')->with('success', 'Product updated successfully.');
            }
            return redirect()->route('seller.dashboard')->with('success', 'Product updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating product: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to update product.']);
        }
    }

    public function destroy(Product $product)
    {
        try {
            if ($product->image_url) {
                Storage::disk('public')->delete($product->image_url);
            }
            foreach ($product->images as $image) {
                Storage::disk('public')->delete($image->image_url);
                $image->delete();
            }

            $product->delete();
            Log::info('Product deleted by admin: ' . auth()->user()->email . ', Product ID: ' . $product->id);
            return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting product: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to delete product.']);
        }
    }
}