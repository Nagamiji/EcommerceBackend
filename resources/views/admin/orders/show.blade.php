@extends('layouts.admin')

@section('title', 'Order Details')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <h1 class="m-0">Order #{{ $order->id }}</h1>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <h5>Order Information</h5>
                <p><strong>User:</strong> {{ $order->user ? $order->user->name : 'Guest' }}</p>
                <p><strong>Total Price:</strong> ${{ number_format($order->total_price, 2) }}</p>
                <p><strong>Status:</strong> 
                    <span class="badge badge-{{ $order->status == 'completed' ? 'success' : ($order->status == 'pending' ? 'warning' : ($order->status == 'shipped' ? 'info' : 'danger')) }}">
                        {{ $order->status }}
                    </span>
                </p>
                <p><strong>Created At:</strong> {{ $order->created_at->format('Y-m-d H:i:s') }}</p>
                <p><strong>Updated At:</strong> {{ $order->updated_at->format('Y-m-d H:i:s') }}</p>

                <h5 class="mt-4">Order Items</h5>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->orderItems as $item)
                            <tr>
                                <td>{{ $item->product ? $item->product->name : 'Unknown' }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>${{ number_format($item->price, 2) }}</td>
                                <td>${{ number_format($item->price * $item->quantity, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <h5 class="mt-4">Payments</h5>
                @if($order->payments->isEmpty())
                    <p>No payments recorded.</p>
                @else
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Method</th>
                                <th>Transaction ID</th>
                                <th>Created At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->payments as $payment)
                                <tr>
                                    <td>{{ $payment->id }}</td>
                                    <td>${{ number_format($payment->amount, 2) }}</td>
                                    <td>{{ $payment->payment_status }}</td>
                                    <td>{{ $payment->payment_method }}</td>
                                    <td>{{ $payment->transaction_id ?? 'N/A' }}</td>
                                    <td>{{ $payment->created_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif

                <h5 class="mt-4">Downloads</h5>
                @if($order->downloads->isEmpty())
                    <p>No downloads available.</p>
                @else
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Download URL</th>
                                <th>Expires At</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->downloads as $download)
                                <tr>
                                    <td>{{ $download->product ? $download->product->name : 'Unknown' }}</td>
                                    <td><a href="{{ $download->download_url }}" target="_blank">Download</a></td>
                                    <td>{{ $download->expires_at ? $download->expires_at->format('Y-m-d H:i:s') : 'No Expiry' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection