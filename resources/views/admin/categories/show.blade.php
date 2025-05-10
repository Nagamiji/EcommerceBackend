@extends('layouts.admin')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Category Details</h1>
                </div>
                <div class="col-sm-6">
                    <a href="{{ route('categories.index') }}" class="btn btn-secondary float-right">Back</a>
                </div>
            </div>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-body">
                    <p><strong>ID:</strong> {{ $category->id }}</p>
                    <p><strong>Name:</strong> {{ $category->name }}</p>
                    <p><strong>Description:</strong> {{ $category->description }}</p>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection