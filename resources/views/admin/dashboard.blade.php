@extends('layouts.admin')

@section('content')
<!-- Content Header -->
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Dashboard</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active">Dashboard</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<section class="content">
    <div class="container-fluid">
        <!-- Small Boxes (Stat boxes) -->
        <div class="row">
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $totalProducts }}</h3>
                        <p>Total Products</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-box"></i>
                    </div>
                    <a href="{{ route('products.index') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $totalCategories }}</h3>
                        <p>Total Categories</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-list"></i>
                    </div>
                    <a href="{{ route('categories.index') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $totalOrders }}</h3>
                        <p>Total Orders</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <a href="{{ route('orders.index') }}" class="small-box-footer">Pending: {{ $pendingPayments }} <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>${{ number_format($totalRevenue, 2) }}</h3>
                        <p>Total Revenue</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <a href="{{ route('orders.index') }}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
                </div>
            </div>
        </div>

        <!-- Info Boxes -->
        <div class="row">
            <div class="col-md-6">
                <div class="info-box">
                    <span class="info-box-icon bg-primary"><i class="fas fa-users"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Users</span>
                        <span class="info-box-number">{{ $totalUsers }}</span>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="info-box">
                    <span class="info-box-icon bg-warning"><i class="fas fa-exclamation-triangle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Low Stock</span>
                        <span class="info-box-number">{{ $lowStockProducts }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Top Selling Products</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="topSellingChart" style="height: 250px;"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Orders Over Time</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="ordersChart" style="height: 250px;"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cards -->
        <div class="row">
            <div class="col-md-6">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Users Overview</h3>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-unbordered">
                            <li class="list-group-item">
                                <b>Total Users:</b> <span class="float-right">{{ $totalUsers }}</span>
                            </li>
                            <li class="list-group-item">
                                <b>Customers:</b> <span class="float-right">{{ $customers }}</span>
                            </li>
                            <li class="list-group-item">
                                <b>Sellers:</b> <span class="float-right">{{ $sellers }}</span>
                            </li>
                            <li class="list-group-item">
                                <b>Admins:</b> <span class="float-right">{{ $admins }}</span>
                            </li>
                            <li class="list-group-item">
                                <b>New Today:</b> <span class="float-right">{{ $newUsersToday }}</span>
                            </li>
                            <li class="list-group-item">
                                <b>Active Carts:</b> <span class="float-right">{{ $activeCarts }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card card-warning">
                    <div class="card-header">
                        <h3 class="card-title">Recent Orders</h3>
                    </div>
                    <div class="card-body">
                        @if($recentOrders->isEmpty())
                            <p class="text-center">No recent orders available.</p>
                        @else
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>User</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentOrders as $order)
                                        <tr>
                                            <td>{{ $order->id }}</td>
                                            <td>{{ $order->user ? $order->user->name : 'Unknown' }}</td>
                                            <td>${{ number_format($order->total_price, 2) }}</td>
                                            <td>
                                                <span class="badge badge-{{ $order->status == 'completed' ? 'success' : ($order->status == 'pending' ? 'warning' : ($order->status == 'shipped' ? 'info' : 'danger')) }}">
                                                    {{ $order->status }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card card-success">
                    <div class="card-header">
                        <h3 class="card-title">Top Selling Products</h3>
                    </div>
                    <div class="card-body">
                        @if($topSellingProducts->isEmpty())
                            <p class="text-center">No sales data available.</p>
                        @else
                            <ul class="list-group">
                                @foreach($topSellingProducts as $item)
                                    <li class="list-group-item">
                                        @if($item->product)
                                            {{ $item->product->name }} (Sold: {{ $item->quantity }})
                                        @else
                                            Product #{{ $item->product_id }} (Sold: {{ $item->quantity }}) - Deleted
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card card-info">
                    <div class="card-header">
                        <h3 class="card-title">Top Categories</h3>
                    </div>
                    <div class="card-body">
                        @if($categoriesWithProducts->isEmpty())
                            <p class="text-center">No categories available.</p>
                        @else
                            <ul class="list-group">
                                @foreach($categoriesWithProducts as $category)
                                    <li class="list-group-item">
                                        {{ $category->name }} (Products: {{ $category->products_count }})
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Chart Scripts -->
<script>
    // Top Selling Products Chart
    const topSellingCtx = document.getElementById('topSellingChart').getContext('2d');
    const topSellingChart = new Chart(topSellingCtx, {
        type: 'bar',
        data: {
            labels: @json(array_map(function ($item) {
                return $item->product ? $item->product->name : "Product #{$item->product_id}";
            }, $topSellingProducts->take(5)->all())),
            datasets: [{
                label: 'Units Sold',
                data: @json(array_map(function ($item) {
                    return $item->quantity;
                }, $topSellingProducts->take(5)->all())),
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Orders Over Time Chart (Mock Data - Replace with real data)
    const ordersCtx = document.getElementById('ordersChart').getContext('2d');
    const ordersChart = new Chart(ordersCtx, {
        type: 'line',
        data: {
            labels: ['2025-05-01', '2025-05-02', '2025-05-03', '2025-05-04', '2025-05-05'],
            datasets: [{
                label: 'Orders',
                data: [10, 15, 12, 18, 20],
                fill: false,
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>
@endsection