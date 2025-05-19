<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Add custom service registrations here if needed
    }

    public function boot(): void
    {
        \Log::info('AppServiceProvider booted');
        // Remove the invalid binding check
        // Optionally log middleware existence for debugging
        if (class_exists(\App\Http\Middleware\Seller::class)) {
            \Log::info('Seller middleware class exists');
        }

        // Add PayPal SDK initialization
        try {
            $clientId = env('PAYPAL_CLIENT_ID');
            $clientSecret = env('PAYPAL_CLIENT_SECRET');
            $mode = env('PAYPAL_MODE', 'sandbox');

            if ($clientId && $clientSecret) {
                $apiContext = new \PayPal\Rest\ApiContext(
                    new \PayPal\Auth\OAuthTokenCredential(
                        $clientId,
                        $clientSecret
                    )
                );

                $apiContext->setConfig([
                    'mode' => $mode,
                    'log.LogEnabled' => true,
                    'log.FileName' => storage_path('logs/paypal.log'),
                    'log.LogLevel' => 'DEBUG',
                    'cache.enabled' => true,
                ]);

                // Store the ApiContext in the session for use in controllers
                \Session::put('apiContext', $apiContext);
                \Log::info('PayPal ApiContext initialized successfully');
            } else {
                \Log::warning('PayPal credentials not found in .env');
            }
        } catch (\Exception $e) {
            \Log::error('Failed to initialize PayPal ApiContext: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}