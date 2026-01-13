<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DuitkuService
{
    protected $merchantCode;
    protected $apiKey;
    protected $baseUrl;
    protected $callbackUrl;
    protected $returnUrl;
    protected $expiryPeriod;

    public function __construct()
    {
        $this->merchantCode = config('duitku.merchant_code');
        $this->apiKey = config('duitku.api_key');
        $isSandbox = config('duitku.sandbox');
        $this->baseUrl = $isSandbox ? config('duitku.base_url.sandbox') : config('duitku.base_url.production');
        $this->callbackUrl = config('duitku.callback_url');
        $this->returnUrl = config('duitku.return_url');
        $this->expiryPeriod = config('duitku.expiry_period');
    }

    /**
     * Get available payment methods
     */
    public function getPaymentMethods($amount)
    {
        $datetime = date('Y-m-d H:i:s');
        $signature = md5($this->merchantCode . $amount . $datetime . $this->apiKey);

        try {
            $response = Http::get($this->baseUrl . '/inquiry', [
                'merchantcode' => $this->merchantCode,
                'amount' => $amount,
                'datetime' => $datetime,
                'signature' => $signature,
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to get payment methods',
                'error' => $response->json()
            ];
        } catch (\Exception $e) {
            Log::error('Duitku Get Payment Methods Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Create payment transaction
     */
    public function createTransaction($order)
    {
        $merchantOrderId = $order->order_number;
        $paymentAmount = (int) $order->total_amount;
        $paymentMethod = 'SP'; // Default to QRIS (Shopee Pay)
        $productDetails = "Order #{$order->order_number} - {$order->restaurant->name}";
        $customerVaName = $order->customer_name;
        $email = $order->customer_email ?? 'customer@resto.com';
        $phoneNumber = $order->customer_phone ?? '08123456789';

        $signature = md5($this->merchantCode . $merchantOrderId . $paymentAmount . $this->apiKey);

        // Build item details
        $itemDetails = [];
        foreach ($order->items as $item) {
            $itemDetails[] = [
                'name' => $item->menu->name,
                'price' => (int) $item->price,
                'quantity' => $item->quantity
            ];
        }

        // Customer detail
        $customerDetail = [
            'firstName' => $customerVaName,
            'email' => $email,
            'phoneNumber' => $phoneNumber,
        ];

        $params = [
            'merchantCode' => $this->merchantCode,
            'paymentAmount' => $paymentAmount,
            'paymentMethod' => $paymentMethod,
            'merchantOrderId' => $merchantOrderId,
            'productDetails' => $productDetails,
            'customerVaName' => $customerVaName,
            'email' => $email,
            'phoneNumber' => $phoneNumber,
            'itemDetails' => $itemDetails,
            'customerDetail' => $customerDetail,
            'callbackUrl' => $this->callbackUrl,
            'returnUrl' => $this->returnUrl,
            'signature' => $signature,
            'expiryPeriod' => $this->expiryPeriod
        ];

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/inquiry', $params);

            Log::info('Duitku Create Transaction Response', [
                'status' => $response->status(),
                'body' => $response->json()
            ]);

            if ($response->successful()) {
                $result = $response->json();
                return [
                    'success' => true,
                    'data' => $result
                ];
            }

            $errorResponse = $response->json();
            return [
                'success' => false,
                'message' => $errorResponse['statusMessage'] ?? 'Failed to create transaction',
                'error' => $errorResponse
            ];
        } catch (\Exception $e) {
            Log::error('Duitku Create Transaction Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Verify callback signature
     */
    public function verifyCallback($merchantCode, $amount, $merchantOrderId, $signature)
    {
        $calculatedSignature = md5($merchantCode . $amount . $merchantOrderId . $this->apiKey);
        return $signature === $calculatedSignature;
    }

    /**
     * Check transaction status
     */
    public function checkTransactionStatus($merchantOrderId)
    {
        $signature = md5($this->merchantCode . $merchantOrderId . $this->apiKey);

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/transactionStatus', [
                        'merchantCode' => $this->merchantCode,
                        'merchantOrderId' => $merchantOrderId,
                        'signature' => $signature
                    ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to check transaction status',
                'error' => $response->json()
            ];
        } catch (\Exception $e) {
            Log::error('Duitku Check Transaction Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
