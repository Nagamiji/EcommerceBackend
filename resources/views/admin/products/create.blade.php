@extends('layouts.admin')

@section('title', 'Create Product')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Create Product (Admin)</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Create Product</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data" id="createProductForm">
                        @csrf
                        <div class="form-group">
                            <label for="name">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required maxlength="255">
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
                            @error('description')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="price">Price <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" max="1000000" name="price" id="price" class="form-control @error('price') is-invalid @enderror" value="{{ old('price') }}" required>
                            @error('price')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="stock_quantity">Stock Quantity <span class="text-danger">*</span></label>
                            <input type="number" min="0" max="100000" name="stock_quantity" id="stock_quantity" class="form-control @error('stock_quantity') is-invalid @enderror" value="{{ old('stock_quantity') }}" required>
                            @error('stock_quantity')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="category_id">Category <span class="text-danger">*</span></label>
                            <select name="category_id" id="category_id" class="form-control @error('category_id') is-invalid @enderror" required>
                                <option value="">Select Category</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="is_public">Is Public <span class="text-danger">*</span></label>
                            <select name="is_public" id="is_public" class="form-control @error('is_public') is-invalid @enderror" required>
                                <option value="1" {{ old('is_public', '1') == '1' ? 'selected' : '' }}>Yes</option>
                                <option value="0" {{ old('is_public') == '0' ? 'selected' : '' }}>No</option>
                            </select>
                            @error('is_public')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="image_url">Primary Image (Max 2MB)</label>
                            <input type="file" name="image_url" id="image_url" class="form-control-file @error('image_url') is-invalid @enderror" accept="image/jpeg,image/png,image/jpg,image/gif">
                            @error('image_url')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="images">Additional Images (Max 2MB each)</label>
                            <input type="file" name="images[]" id="images" class="form-control-file @error('images.*') is-invalid @enderror" multiple accept="image/jpeg,image/png,image/jpg,image/gif">
                            @error('images.*')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-primary">Create Product</button>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>

@section('scripts')
<script>
    document.getElementById('createProductForm').addEventListener('submit', function (e) {
        const imageInput = document.getElementById('image_url');
        const imagesInput = document.getElementById('images');
        const maxSize = 2 * 1024 * 1024; // 2MB

        if (imageInput.files.length > 0 && imageInput.files[0].size > maxSize) {
            e.preventDefault();
            alert('Primary image must be less than 2MB.');
            return;
        }

        if (imagesInput.files.length > 0) {
            for (let file of imagesInput.files) {
                if (file.size > maxSize) {
                    e.preventDefault();
                    alert('Each additional image must be less than 2MB.');
                    return;
                }
            }
        }
    });
</script>
@endsection