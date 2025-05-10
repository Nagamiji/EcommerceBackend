<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::all();
        return response()->json($orders);
    }

    public function indexAdmin()
    {
        return view('admin.orders');
    }

    public function store(Request $request)
    {
        $order = Order::create($request->all());
        return response()->json($order, 201);
    }

    public function show($id)
    {
        $order = Order::find($id);
        if ($order) {
            return response()->json($order);
        } else {
            return response()->json(['message' => 'Order not found'], 404);
        }
    }

    public function update(Request $request, $id)
    {
        $order = Order::find($id);
        if ($order) {
            $order->update($request->all());
            return response()->json($order);
        } else {
            return response()->json(['message' => 'Order not found'], 404);
        }
    }

    public function destroy($id)
    {
        $order = Order::find($id);
        if ($order) {
            $order->delete();
            return response()->json(['message' => 'Order deleted successfully']);
        } else {
            return response()->json(['message' => 'Order not found'], 404);
        }
    }
}