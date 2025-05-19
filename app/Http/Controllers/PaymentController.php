<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Download;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;

class PaymentController extends Controller
{
    protected $client;

    public function __construct()
    {
        $environment = new SandboxEnvironment(env('PAYPAL_CLIENT_ID'), env('PAYPAL_CLIENT_SECRET'));
        $this->client = new PayPalHttpClient($environment);
    }

    public function capturePayment(Request $request)
    {
        $request->validate([
            'paypal_order_id' => 'required|string',
            'order_id' => 'required|exists:orders,id',
        ]);

        try {
            DB::beginTransaction();

            $paypalOrderId = $request->paypal_order_id;
            $order = Order::findOrFail($request->order_id);
            $payment = Payment::where('order_id', $order->id)->firstOrFail();

            $request = new OrdersCaptureRequest($paypalOrderId);
            $response = $this->client->execute($request);

            if ($response->result->status === 'COMPLETED') {
                $payment->update([
                    'payment_status' => 'completed',
                    'transaction_id' => $paypalOrderId,
                ]);

                $order->update(['status' => 'completed']);

                $orderItems = $order->orderItems;
                foreach ($orderItems as $item) {
                    $product = Product::find($item->product_id);
                    $product->decrement('stock_quantity', $item->quantity);
                }

                $downloadLinks = [];
                foreach ($orderItems as $item) {
                    $product = Product::find($item->product_id);
                    $url = $this->generateDownloadUrl($product);
                    Download::create([
                        'order_id' => $order->id,
                        'product_id' => $item->product_id,
                        'download_url' => $url,
                        'expires_at' => now()->addDays(7),
                    ]);
                    $downloadLinks[] = $url;
                }

                DB::commit();

                return response()->json([
                    'status' => 'completed',
                    'order_id' => $order->id,
                    'download_links' => $downloadLinks,
                ], 200);
            } else {
                $payment->update(['payment_status' => 'pending']);
                DB::commit();
                return response()->json(['status' => 'pending'], 200);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment capture failed: ' . $e->getMessage());
            return response()->json(['error' => 'Payment capture failed'], 500);
        }
    }

    private function generateDownloadUrl($product)
    {
        return "http://localhost:8001/storage/products/{$product->id}/game.zip";
    }
}