<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\Payment;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{
    protected $apiContext;

    public function __construct()
    {
        if (\Session::has('apiContext')) {
            $this->apiContext = \Session::get('apiContext');
        }
    }

    public function checkout(Request $request)
    {
        try {
            $user = auth()->user();
            Log::info('Starting checkout process', ['user' => $user ? $user->toArray() : null]);

            if (!$user) {
                return response()->json(['error' => 'User not authenticated'], 401);
            }

            $cartItems = Cart::where('user_id', $user->id)->with('product')->get();

            if ($cartItems->isEmpty()) {
                return response()->json(['error' => 'Cart is empty'], 400);
            }

            $totalPrice = $cartItems->sum(function ($item) {
                return $item->quantity * $item->product->price;
            });

            $order = Order::create([
                'user_id' => $user->id,
                'total_price' => $totalPrice,
                'status' => 'pending',
            ]);

            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->product->price,
                ]);
            }

            $payment = new \PayPal\Api\Payment();
            $payer = new \PayPal\Api\Payer();
            $payer->setPaymentMethod('paypal');

            $amount = new \PayPal\Api\Amount();
            $amount->setTotal($totalPrice)->setCurrency('USD');

            $transaction = new \PayPal\Api\Transaction();
            $transaction->setAmount($amount);

            $redirectUrls = new \PayPal\Api\RedirectUrls();
            $redirectUrls->setReturnUrl(url('/api/order/paypal/success'))
                         ->setCancelUrl(url('/api/order/paypal/cancel'));

            $payment->setIntent('sale')
                    ->setPayer($payer)
                    ->setTransactions([$transaction])
                    ->setRedirectUrls($redirectUrls);

            $payment->create($this->apiContext);

            Log::info('Order created', ['order_id' => $order->id]);
            Log::info('PayPal payment created', ['payment_id' => $payment->getId()]);

            Payment::create([
                'order_id' => $order->id,
                'transaction_id' => $payment->getId(),
                'payment_status' => 'pending',
                'amount' => $totalPrice,
                'payment_method' => 'paypal',
            ]);

            return response()->json([
                'paypal_payment_id' => $payment->getId(),
                'order_id' => $order->id,
                'approval_url' => $payment->getApprovalLink(),
            ], 200);
        } catch (\Exception $e) {
            Log::error('Checkout failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['error' => 'Checkout failed'], 500);
        }
    }

    public function success(Request $request)
    {
        Log::info('Processing success callback', [
            'paymentId' => $request->query('paymentId'),
            'paymentType' => $request->query('paymentType'),
            'payerId' => $request->query('PayerID')
        ]);

        $paymentId = $request->query('paymentId');
        $payerId = $request->query('PayerID');
        $paymentType = $request->query('paymentType', 'paypal');

        if (!$paymentId || !$payerId) {
            Log::error('Missing paymentId or PayerID in success callback');
            return response()->json(['error' => 'Invalid payment data'], 400);
        }

        try {
            $payment = \PayPal\Api\Payment::get($paymentId, $this->apiContext);
            $execution = new \PayPal\Api\PaymentExecution();
            $execution->setPayerId($payerId);

            $result = $payment->execute($execution, $this->apiContext);
            Log::info('Payment executed successfully', ['payment_id' => $paymentId]);

            $paymentRecord = Payment::where('transaction_id', $paymentId)->firstOrFail();
            $order = Order::findOrFail($paymentRecord->order_id);

            $newStatus = ($paymentType === 'paylater') ? 'pending' : 'completed';
            $paymentRecord->update(['payment_status' => $newStatus]);
            $order->update(['status' => $newStatus]);

            Log::info('Order status updated', [
                'order_id' => $order->id,
                'new_status' => $newStatus,
                'payment_type' => $paymentType,
            ]);

            $user = auth()->user();
            if ($user) {
                Cart::where('user_id', $user->id)->delete();
            }

            return response()->json(['message' => 'Payment successful', 'order_id' => $order->id, 'status' => $newStatus], 200);
        } catch (\Exception $e) {
            Log::error('Payment execution failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['error' => 'Payment execution failed'], 500);
        }
    }

    public function payLater(Request $request)
    {
        try {
            $user = auth()->user();
            Log::info('Starting Pay Later checkout process', ['user' => $user ? $user->toArray() : null]);

            if (!$user) {
                Log::error('User not authenticated in payLater');
                return response()->json(['error' => 'User not authenticated'], 401);
            }

            $cartItems = Cart::where('user_id', $user->id)->with('product')->get();

            if ($cartItems->isEmpty()) {
                Log::warning('Cart is empty during Pay Later checkout', ['user_id' => $user->id]);
                return response()->json(['error' => 'Cart is empty'], 400);
            }

            $totalPrice = $cartItems->sum(function ($item) {
                return $item->quantity * $item->product->price;
            });

            $order = Order::create([
                'user_id' => $user->id,
                'total_price' => $totalPrice,
                'status' => 'pending',
            ]);

            foreach ($cartItems as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'price' => $item->product->price,
                ]);
            }

            $payment = Payment::create([
                'order_id' => $order->id,
                'transaction_id' => 'PAYLATER-' . $order->id . '-' . time(),
                'payment_status' => 'pending',
                'amount' => $totalPrice,
                'payment_method' => 'paylater',
            ]);

            Cart::where('user_id', $user->id)->delete();

            Log::info('Pay Later order created', [
                'order_id' => $order->id,
                'total_price' => $totalPrice,
                'payment_id' => $payment->id,
            ]);

            return response()->json([
                'message' => 'Pay Later order placed successfully',
                'order_id' => $order->id,
                'status' => 'pending',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Pay Later checkout failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['error' => 'Pay Later checkout failed'], 500);
        }
    }
}