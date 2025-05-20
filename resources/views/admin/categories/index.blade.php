@extends('layouts.admin')

@section('title', 'Categories')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Categories</h1>
                </div>
                <div class="col-sm-6">
                    <a href="{{ url('/admin/categories/create') }}" class="btn btn-primary float-right">Add New Category</a>
                </div>
            </div>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid">
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif
            <div class="card">
                <div class="card-body">
                    <p>Debug: Categories count = {{ $categories->count() }}</p>
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Products</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($categories as $category)
                                <tr>
                                    <td>{{ $category->id }}</td>
                                    <td>{{ $category->name }}</td>
                                    <td>{{ $category->description ?? 'None' }}</td>
                                    <td>{{ $category->products_count ?? 0 }}</td>
                                    <td>
                                        <a href="{{ url('/admin/categories/' . $category->id) }}" class="btn btn-info btn-sm">View</a>
                                        <a href="{{ url('/admin/categories/' . $category->id . '/edit') }}" class="btn btn-warning btn-sm">Edit</a>
                                        <form action="{{ url('/admin/categories/' . $category->id) }}" method="POST" style="display:inline;" class="delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">No categories available.</td>
                                </tr>
                            @endforelse
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.delete-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                if (confirm('Are you sure you want to delete this category?')) {
                    this.submit();
                }
            });
        });
    });
</script>
@endsection