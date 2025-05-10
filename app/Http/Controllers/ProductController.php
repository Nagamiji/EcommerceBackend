<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProductController extends Controller
{
    public function publicIndex()
    {
        $products = Product::where('is_public', true)->get();
        return response()->json($products);
    }

    public function index()
    {
        return response()->json(Product::all());
    }

    public function indexAdmin()
    {
        return view('admin.products');
    }

    public function store(Request $request)
    {
        Log::info('Product store request', $request->all());
        Log::info('Authenticated user ID', ['user_id' => auth()->check() ? auth()->id() : 'unauthenticated']);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'user_id' => 'required|exists:users,id',
            'is_public' => 'required|boolean',
        ], [
            'category_id.exists' => 'The specified category does not exist.',
            'user_id.exists' => 'The specified user does not exist.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'details' => $validator->errors()
            ], 422);
        }

        if ($request->user_id != auth()->id()) {
            return response()->json([
                'error' => 'Unauthorized',
                'details' => 'user_id does not match authenticated user (expected: ' . auth()->id() . ', got: ' . $request->user_id . ')'
            ], 403);
        }

        try {
            $validated = $validator->validated();
            $product = Product::create($validated);
            return response()->json([
                'message' => 'Product created successfully',
                'product' => $product
            ], 201);
        } catch (\Exception $e) {
            Log::error('Product creation failed', ['error' => $e->getMessage()]);
            return response()->json([
                'error' => 'Failed to create product',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        Log::info('Show request for ID', ['id' => $id, 'auth_check' => auth()->check() ? auth()->id() : 'unauthenticated']);
        try {
            $product = Product::findOrFail($id);
            return response()->json($product);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Product not found',
                'details' => "No product exists with ID: {$id}"
            ], 404);
        } catch (\Exception $e) {
            Log::error('Product show failed', ['error' => $e->getMessage()]);
            return response()->json([
                'error' => 'Failed to retrieve product',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        Log::info('Update request received', ['id' => $id, 'auth_check' => auth()->check() ? auth()->id() : 'unauthenticated']);
        try {
            $product = Product::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|string|max:255',
                'description' => 'sometimes|nullable|string',
                'price' => 'sometimes|numeric|min:0',
                'stock_quantity' => 'sometimes|integer|min:0',
                'category_id' => 'sometimes|exists:categories,id',
                'user_id' => 'sometimes|exists:users,id',
                'is_public' => 'sometimes|boolean',
            ], [
                'category_id.exists' => 'The specified category does not exist.',
                'user_id.exists' => 'The specified user does not exist.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'Validation failed',
                    'details' => $validator->errors()
                ], 422);
            }

            if ($request->user_id != auth()->id()) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'details' => 'user_id does not match authenticated user (expected: ' . auth()->id() . ', got: ' . $request->user_id . ')'
                ], 403);
            }

            $product->update($validator->validated());
            return response()->json([
                'message' => 'Product updated successfully',
                'product' => $product
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Product not found',
                'details' => "No product exists with ID: {$id}"
            ], 404);
        } catch (\Exception $e) {
            Log::error('Product update failed', ['error' => $e->getMessage()]);
            return response()->json([
                'error' => 'Failed to update product',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        Log::info('Destroy request for ID', ['id' => $id, 'auth_check' => auth()->check() ? auth()->id() : 'unauthenticated']);
        try {
            $product = Product::findOrFail($id);
            $product->delete();
            return response()->json([
                'message' => 'Product deleted successfully'
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Product not found',
                'details' => "No product exists with ID: {$id}"
            ], 404);
        } catch (\Exception $e) {
            Log::error('Product deletion failed', ['error' => $e->getMessage()]);
            return response()->json([
                'error' => 'Failed to delete product',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}