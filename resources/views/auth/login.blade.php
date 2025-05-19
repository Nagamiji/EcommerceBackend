<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;

class CheckoutController extends Controller
{
    protected $client;

    public function __construct()
    {
        $environment = new SandboxEnvironment(env('PAYPAL_CLIENT_ID'), env('PAYPAL_CLIENT_SECRET'));
        $this->client = new PayPalHttpClient($environment);
    }

    public function checkout(Request $request)
    {
        try {
            Log::info('Starting checkout process', ['user_id' => auth()->id()]);
            
            $user = auth()->user();
            if (!$user) {
                Log::error('Unauthorized access');
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            // Simulate cart retrieval (adjust based on your models)
            $cartItems = $user->cart()->with('product')->get();
            if ($cartItems->isEmpty()) {
                Log::error('Cart is empty');
                return response()->json(['error' => 'Cart is empty'], 400);
            }

            $totalPrice = $cartItems->sum(fn($item) => $item->quantity * $item->product->price);

            $paypalRequest = new OrdersCreateRequest();
            $paypalRequest->prefer('return=representation');
            $paypalRequest->body = [
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'amount' => [
                        'currency_code' => 'USD',
                        'value' => number_format($totalPrice, 2, '.', ''),
                    ],
                ]],
                'application_context' => [
                    'return_url' => 'http://localhost:8000/order-success',
                    'cancel_url' => 'http://localhost:8000/cart',
                ],
            ];

            Log::info('PayPal request prepared', ['body' => $paypalRequest->body]);
            $response = $this->client->execute($paypalRequest);
            Log::info('PayPal order created', ['paypal_order_id' => $response->result->id]);

            return response()->json(['paypal_order_id' => $response->result->id], 200);
        } catch (\Exception $e) {
            Log::error('Checkout failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['error' => 'Checkout failed'], 500);
        }
    }
}