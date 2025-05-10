@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="row mb-3">
        <div class="col-md-12 text-end">
            <button id="logout-btn" class="btn btn-danger">Logout</button>
        </div>
    </div>
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
    </div>

    <script>
        const token = localStorage.getItem('token');
        if (!token) {
            console.log('No token found, redirecting to login');
            window.location.href = '{{ route('login') }}';
        } else {
            console.log('Token:', token);
        }

        // Logout functionality
        document.getElementById('logout-btn').addEventListener('click', function () {
            fetch('/api/logout', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => {
                console.log('Logout Response:', response.status, response.statusText);
                if (!response.ok) throw new Error('Logout failed');
                return response.json();
            })
            .then(data => {
                console.log('Logout response:', data);
                localStorage.removeItem('token');
                window.location.href = '{{ route('login') }}';
            })
            .catch(error => {
                console.error('Error during logout:', error);
                localStorage.removeItem('token');
                window.location.href = '{{ route('login') }}';
            });
        });

        // Fetch Products
        fetch('/api/products', {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(response => {
            console.log('Products Request Headers:', {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            });
            console.log('Products Response:', response.status, response.statusText);
            if (!response.ok) {
                return response.text().then(text => {
                    throw new Error(`Products fetch failed: ${response.status} ${response.statusText} - ${text}`);
                });
            }
            return response.json();
        })
        .then(data => {
            console.log('Products Data:', data);
            document.getElementById('products-count').textContent = data.length || 0;
        })
        .catch(error => {
            console.error('Error fetching products:', error);
            window.location.href = '{{ route('login') }}';
        });

        // Fetch Categories
        fetch('/api/categories', {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(response => {
            console.log('Categories Request Headers:', {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            });
            console.log('Categories Response:', response.status, response.statusText);
            if (!response.ok) {
                return response.text().then(text => {
                    throw new Error(`Categories fetch failed: ${response.status} ${response.statusText} - ${text}`);
                });
            }
            return response.json();
        })
        .then(data => {
            console.log('Categories Data:', data);
            document.getElementById('categories-count').textContent = data.length || 0;
        })
        .catch(error => {
            console.error('Error fetching categories:', error);
            window.location.href = '{{ route('login') }}';
        });

        // Fetch Orders
        fetch('/api/orders', {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
        .then(response => {
            console.log('Orders Request Headers:', {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            });
            console.log('Orders Response:', response.status, response.statusText);
            if (!response.ok) {
                return response.text().then(text => {
                    throw new Error(`Orders fetch failed: ${response.status} ${response.statusText} - ${text}`);
                });
            }
            return response.json();
        })
        .then(data => {
            console.log('Orders Data:', data);
            document.getElementById('orders-count').textContent = data.length || 0;
        })
        .catch(error => {
            console.error('Error fetching orders:', error);
            window.location.href = '{{ route('login') }}';
        });
    </script>
@endsection