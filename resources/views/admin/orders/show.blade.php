@extends('layouts.admin')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Order Details</h1>
                </div>
                <div class="col-sm-6">
                    <a href="{{ route('orders.index') }}" class="btn btn-secondary float-right">Back</a>
                </div>
            </div>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-body">
                    <p><strong>ID:</strong> {{ $order->id }}</p>
                    <p><strong>User:</strong> {{ $order->user ? $order->user->name : 'Unknown' }}</p>
                    <p><strong>Total Price:</strong> ${{ number_format($order->total_price, 2) }}</p>
                    <p><strong>Status:</strong> <span class="badge badge-{{ $order->status == 'completed' ? 'success' : ($order->status == 'pending' ? 'warning' : ($order->status == 'shipped' ? 'info' : 'danger')) }}">{{ $order->status }}</span></p>
                    <h5>Order Items:</h5>
                    @if($order->orderItems->isEmpty())
                        <p>No items in this order.</p>
                    @else
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Product ID</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->orderItems as $item)
                                    <tr>
                                        <td>{{ $item->product_id }}</td>
                                        <td>{{ $item->quantity }}</td>
                                        <td>${{ number_format($item->price, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </section>
</div>
@endsection