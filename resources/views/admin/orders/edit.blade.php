@extends('layouts.admin')

@section('title', 'Edit Order')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <h1 class="m-0">Edit Order</h1>
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
                <form action="{{ route('orders.update', $order->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="form-group">
                        <label for="user_id">User</label>
                        <select class="form-control" id="user_id" name="user_id">
                            <option value="">Select User (Optional)</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ $order->user_id == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select class="form-control" id="status" name="status" required>
                            <option value="pending" {{ $order->status == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="shipped" {{ $order->status == 'shipped' ? 'selected' : '' }}>Shipped</option>
                            <option value="completed" {{ $order->status == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ $order->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Order Items</label>
                        <div id="order-items">
                            @foreach($order->orderItems as $index => $item)
                                <div class="row mb-2">
                                    <div class="col-md-6">
                                        <select class="form-control" name="items[{{ $index }}][product_id]" required>
                                            <option value="{{ $item->product_id }}">{{ $item->product->name }}</option>
                                            @foreach($products as $product)
                                                @if($product->id != $item->product_id)
                                                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <input type="number" class="form-control" name="items[{{ $index }}][quantity]" value="{{ $item->quantity }}" min="1" required>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="button" class="btn btn-danger" onclick="this.parentElement.parentElement.remove()">Remove</button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-secondary mt-2" onclick="addItemRow()">Add Item</button>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Order</button>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection

@section('scripts')
<script>
    let itemIndex = {{ count($order->orderItems) }};
    function addItemRow() {
        const container = document.getElementById('order-items');
        const row = document.createElement('div');
        row.className = 'row mb-2';
        row.innerHTML = `
            <div class="col-md-6">
                <select class="form-control" name="items[${itemIndex}][product_id]" required>
                    <option value="">Select Product</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <input type="number" class="form-control" name="items[${itemIndex}][quantity]" min="1" placeholder="Quantity" required>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-danger" onclick="this.parentElement.parentElement.remove()">Remove</button>
            </div>
        `;
        container.appendChild(row);
        itemIndex++;
    }
</script>
@endsection