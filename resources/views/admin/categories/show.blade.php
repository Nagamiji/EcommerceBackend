@extends('layouts.admin')

@section('title', 'View Category')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">View Category</h1>
                </div>
                <div class="col-sm-6">
                    <a href="{{ route('categories.index') }}" class="btn btn-secondary float-right">Back</a>
                </div>
            </div>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid">
            @if(isset($category))
                <div class="card">
                    <div class="card-body">
                        <p><strong>ID:</strong> {{ $category->id }}</p>
                        <p><strong>Name:</strong> {{ $category->name }}</p>
                        <p><strong>Description:</strong> {{ $category->description ?? 'None' }}</p>
                        <p><strong>Products Count:</strong> {{ $category->products_count ?? 0 }}</p>
                    </div>
                </div>
            @else
                <div class="alert alert-danger">
                    Category not found or an error occurred.
                </div>
            @endif
        </div>
    </section>
</div>
@endsection