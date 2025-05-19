<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        Log::info('CategoryController@index called', [
            'user_id' => Auth::id(),
            'email' => Auth::user()->email ?? 'guest',
            'session_id' => session()->getId(),
            'is_admin' => Auth::user()->is_admin ?? false,
            'expects_json' => $request->expectsJson(),
        ]);

        if ($request->expectsJson()) {
            $perPage = $request->input('per_page', 10);
            $search = $request->input('search', '');
            $categories = Category::withCount('products')
                ->when($search, function ($query, $search) {
                    return $query->where('name', 'ilike', "%{$search}%");
                })
                ->paginate($perPage);

            Log::info('CategoryController: Returning categories JSON', [
                'count' => $categories->total(),
                'user_id' => Auth::id(),
                'session_id' => session()->getId(),
            ]);

            return response()->json([
                'status' => 'success',
                'data' => $categories->items(),
                'meta' => [
                    'total' => $categories->total(),
                    'current_page' => $categories->currentPage(),
                    'last_page' => $categories->lastPage(),
                    'per_page' => $categories->perPage(),
                    'links' => [
                        'first' => $categories->url(1),
                        'last' => $categories->url($categories->lastPage()),
                        'prev' => $categories->previousPageUrl(),
                        'next' => $categories->nextPageUrl(),
                    ],
                ],
            ], 200);
        }

        $categories = Category::withCount('products')->get();
        Log::info('CategoryController: Displaying categories index', [
            'count' => $categories->count(),
            'user_id' => Auth::id(),
            'session_id' => session()->getId(),
        ]);

        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255|unique:categories,name',
                'description' => 'nullable|string|max:1000',
            ], [
                'name.required' => 'Category name is required.',
                'name.unique' => 'This category name already exists.',
                'name.max' => 'Category name must not exceed 255 characters.',
                'description.max' => 'Description must not exceed 1000 characters.',
            ]);

            $category = Category::create($request->only('name', 'description'));
            return $request->expectsJson()
                ? response()->json(['status' => 'success', 'message' => 'Category created successfully', 'data' => $category], 201)
                : redirect()->route('categories.index')->with('success', 'Category created successfully.');
        } catch (ValidationException $e) {
            return $request->expectsJson()
                ? response()->json(['status' => 'error', 'errors' => $e->errors()], 422)
                : back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('CategoryController@store failed', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'session_id' => session()->getId(),
            ]);
            return $request->expectsJson()
                ? response()->json(['status' => 'error', 'message' => 'Failed to create category'], 500)
                : back()->with('error', 'Failed to create category. Please try again.')->withInput();
        }
    }

    public function show($id)
    {
        try {
            $category = Category::withCount('products')->findOrFail($id);
            return response()->json([
                'status' => 'success',
                'data' => $category,
            ], 200);
        } catch (\Exception $e) {
            Log::error('CategoryController@show failed', [
                'error' => $e->getMessage(),
                'category_id' => $id,
                'user_id' => Auth::id() ?? 'guest',
                'session_id' => session()->getId(),
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Category not found.',
            ], 404);
        }
    }

    public function showWeb(Category $category)
    {
        $category->loadCount('products');
        return view('admin.categories.show', compact('category'));
    }

    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
                'description' => 'nullable|string|max:1000',
            ], [
                'name.required' => 'Category name is required.',
                'name.unique' => 'This category name already exists.',
                'name.max' => 'Category name must not exceed 255 characters.',
                'description.max' => 'Description must not exceed 1000 characters.',
            ]);

            $category->update($request->only('name', 'description'));
            return $request->expectsJson()
                ? response()->json(['status' => 'success', 'message' => 'Category updated successfully', 'data' => $category], 200)
                : redirect()->route('categories.index')->with('success', 'Category updated successfully.');
        } catch (ValidationException $e) {
            return $request->expectsJson()
                ? response()->json(['status' => 'error', 'errors' => $e->errors()], 422)
                : back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('CategoryController@update failed', [
                'error' => $e->getMessage(),
                'category_id' => $category->id,
                'user_id' => Auth::id(),
                'session_id' => session()->getId(),
            ]);
            return $request->expectsJson()
                ? response()->json(['status' => 'error', 'message' => 'Failed to update category'], 500)
                : back()->with('error', 'Failed to update category. Please try again.')->withInput();
        }
    }

    public function destroy(Category $category)
    {
        try {
            if ($category->products()->exists()) {
                return request()->expectsJson()
                    ? response()->json(['status' => 'error', 'message' => 'Cannot delete category with associated products'], 400)
                    : back()->with('error', 'Cannot delete category with associated products.');
            }

            $category->delete();
            return request()->expectsJson()
                ? response()->json(['status' => 'success', 'message' => 'Category deleted successfully'], 200)
                : redirect()->route('categories.index')->with('success', 'Category deleted successfully.');
        } catch (\Exception $e) {
            Log::error('CategoryController@destroy failed', [
                'error' => $e->getMessage(),
                'category_id' => $category->id,
                'user_id' => Auth::id(),
                'session_id' => session()->getId(),
            ]);
            return request()->expectsJson()
                ? response()->json(['status' => 'error', 'message' => 'Failed to delete category'], 500)
                : back()->with('error', 'Failed to delete category. Please try again.');
        }
    }

    public function publicIndex(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10);
            $search = $request->input('search', '');
            $categories = Category::with(['products' => function ($query) {
                $query->select('id', 'name', 'price', 'image_url')->where('is_public', true);
            }])
                ->select('id', 'name', 'description')
                ->when($search, function ($query, $search) {
                    return $query->where('name', 'ilike', "%{$search}%");
                })
                ->paginate($perPage);

            Log::info('CategoryController@publicIndex called', [
                'user_id' => Auth::id() ?? 'guest',
                'session_id' => session()->getId(),
                'per_page' => $perPage,
                'total_categories' => $categories->total(),
                'search' => $search,
            ]);

            return response()->json([
                'status' => 'success',
                'data' => $categories->items(),
                'meta' => [
                    'total' => $categories->total(),
                    'current_page' => $categories->currentPage(),
                    'last_page' => $categories->lastPage(),
                    'per_page' => $categories->perPage(),
                    'links' => [
                        'first' => $categories->url(1),
                        'last' => $categories->url($categories->lastPage()),
                        'prev' => $categories->previousPageUrl(),
                        'next' => $categories->nextPageUrl(),
                    ],
                ],
            ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        } catch (\Exception $e) {
            Log::error('CategoryController@publicIndex failed', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id() ?? 'guest',
                'session_id' => session()->getId(),
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to fetch categories.',
            ], 500);
        }
    }
}