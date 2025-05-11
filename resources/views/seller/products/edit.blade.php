@extends('layouts.admin')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Edit Product</h1>
                </div>
            </div>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid">
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('seller.products.update', $product->id) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $product->name) }}" required>
                        </div>
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea name="description" class="form-control">{{ old('description', $product->description) }}</textarea>
                        </div>
                        <div class="form-group">
                            <label for="price">Price</label>
                            <input type="number" step="0.01" name="price" class="form-control" value="{{ old('price', $product->price) }}" required>
                        </div>
                        <div class="form-group">
                            <label for="stock_quantity">Stock Quantity</label>
                            <input type="number" name="stock_quantity" class="form-control" value="{{ old('stock_quantity', $product->stock_quantity) }}" required>
                        </div>
                        <div class="form-group">
                            <label for="category_id">Category</label>
                            <select name="category_id" class="form-control" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="is_public">Is Public</label>
                            <select name="is_public" class="form-control" required>
                                <option value="1" {{ old('is_public', $product->is_public) ? 'selected' : '' }}>Yes</option>
                                <option value="0" {{ old('is_public', $product->is_public) ? '' : 'selected' }}>{{ 'No' }}</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="image_url">Primary Image</label>
                            @if($product->image_url)
                                <img src="{{ $product->primary_image_url }}" alt="{{ $product->name }}" style="width: 100px; height: 100px; object-fit: cover;">
                            @endif
                            <input type="file" name="image_url" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="images">Additional Images</label>
                            @if($product->images->isNotEmpty())
                                @foreach($product->images as $image)
                                    <img src="{{ Storage::url($image->image_url) }}" alt="Additional Image" style="width: 100px; height: 100px; object-fit: cover;">
                                @endforeach
                            @endif
                            <input type="file" name="images[]" class="form-control" multiple>
                        </div>
                        <button type="submit" class="btn btn-primary">Update Product</button>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection