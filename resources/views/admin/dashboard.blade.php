@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="row">
    <div class="col-md-4 mb-4">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <h5 class="card-title">Products</h5>
                <h2 id="products-count" class="card-text">0</h2>
                <a href="{{ route('admin.products') }}" class="btn btn-light">View Details</a>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card text-white bg-success">
            <div class="card-body">
                <h5 class="card-title">Categories</h5>
                <h2 id="categories-count" class="card-text">0</h2>
                <a href="{{ route('admin.categories') }}" class="btn btn-light">View Details</a>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <h5 class="card-title">Orders</h5>
                <h2 id="orders-count" class="card-text">0</h2>
                <a href="{{ route('admin.orders') }}" class="btn btn-light">View Details</a>
            </div>
        </div>
    </div>

    <!-- Logout Button -->
    <div class="text-end mt-3">
        <button id="logout-btn" class="btn btn-danger">Logout</button>
    </div>
</div>

<script>
    const token = localStorage.getItem('token');
    if (!token) {
        window.location.href = '{{ route('login') }}';
    } else {
        console.log('Token from localStorage:', token);
    }

    // Logout functionality
    document.getElementById('logout-btn').addEventListener('click', function () {
        fetch('/api/logout', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Logout failed');
            return response.json();
        })
        .then(() => {
            localStorage.removeItem('token');
            window.location.href = '{{ route('login') }}';
        })
        .catch(error => {
            console.error('Logout error:', error);
            localStorage.removeItem('token');
            window.location.href = '{{ route('login') }}';
        });
    });

    // Fetch counts
    ['products', 'categories', 'orders'].forEach(endpoint => {
        fetch(`/api/${endpoint}`, {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`Failed to fetch ${endpoint}: ${response.status} ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            document.getElementById(`${endpoint}-count`).textContent = data.length || 0;
            console.log(`${endpoint} Data:`, data);
        })
        .catch(error => {
            console.error(`Error fetching ${endpoint}:`, error);
            window.location.href = '{{ route('login') }}';
        });
    });
</script>
@endsection