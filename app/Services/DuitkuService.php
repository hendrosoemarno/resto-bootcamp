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
    protected $isSandbox;

    public function __construct()
    {
        $this->merchantCode = config('duitku.merchant_code');
        $this->apiKey = config('duitku.api_key');
        $this->isSandbox = config('duitku.sandbox');
        $this->baseUrl = $this->isSandbox ? config('duitku.base_url.sandbox') : config('duitku.base_url.production');
        $this->callbackUrl = config('duitku.callback_url');
        $this->returnUrl = config('duitku.return_url');
        $this->expiryPeriod = config('duitku.expiry_period');
    }

    /**
     * Get available payment methods
     * Note: Signature uses SHA256 for this endpoint
     */
    public function getPaymentMethods($amount)
    {
        $amount = (int) $amount;
        $datetime = date('Y-m-d H:i:s');

        // SHA256: merchantCode + amount + datetime + apiKey
        $signature = hash('sha256', $this->merchantCode . $amount . $datetime . $this->apiKey);

        $url = $this->isSandbox
            ? 'https://sandbox.duitku.com/webapi/api/merchant/paymentmethod/getpaymentmethod'
            : 'https://passport.duitku.com/webapi/api/merchant/paymentmethod/getpaymentmethod';

        try {
            $response = Http::withHeaders(['Content-Type' => 'application/json'])
                ->post($url, [
                    'merchantcode' => $this->merchantCode,
                    'amount' => $amount,
                    'datetime' => $datetime,
                    'signature' => $signature,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['paymentFee'] ?? [];
            }
            return [];
        } catch (\Exception $e) {
            Log::error('Duitku Get Payment Methods Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Create payment transaction
     * Note: Signature uses MD5 for this endpoint
     */
    public function createTransaction($order, $paymentMethod = null)
    {
        $merchantOrderId = $order->order_number;
        $paymentAmount = (int) $order->total_amount;
        $paymentMethod = $paymentMethod ?? config('duitku.payment_method', 'DQ');
        $productDetails = "Order #{$order->order_number} at " . ($order->restaurant->name ?? 'Resto');

        // Signature: merchantCode + merchantOrderId + paymentAmount + apiKey
        $signature = md5($this->merchantCode . $merchantOrderId . $paymentAmount . $this->apiKey);

        // Cleaning Customer Name (Hanya Huruf)
        $firstName = preg_replace('/[^a-zA-Z ]/', '', $order->customer_name ?? 'Pelanggan');
        $firstName = substr($firstName, 0, 20);

        // Cleaning Phone Number
        $phoneNumber = '081234567890'; // Default jika kosong

        $itemDetails = [];
        foreach ($order->items as $item) {
            $itemDetails[] = [
                'name' => substr($item->menu->name, 0, 50),
                'price' => (int) $item->price,
                'quantity' => $item->quantity
            ];
        }

        $params = [
            'merchantCode' => $this->merchantCode,
            'paymentAmount' => $paymentAmount,
            'paymentMethod' => $paymentMethod,
            'merchantOrderId' => $merchantOrderId,
            'productDetails' => $productDetails,
            'additionalParam' => '',
            'merchantUserInfo' => $order->customer_name,
            'customerVaName' => $firstName,
            'email' => 'customer@gmail.com',
            'phoneNumber' => $phoneNumber,
            'itemDetails' => $itemDetails,
            'callbackUrl' => $this->callbackUrl,
            'returnUrl' => str_replace('{orderNumber}', $order->order_number, $this->returnUrl),
            'expiryPeriod' => $this->expiryPeriod,
            'signature' => $signature
        ];

        try {
            $response = Http::withHeaders(['Content-Type' => 'application/json'])
                ->post($this->baseUrl . '/inquiry', $params);

            $responseBody = $response->json();

            if ($response->successful() && isset($responseBody['paymentUrl'])) {
                return [
                    'success' => true,
                    'data' => $responseBody
                ];
            }

            Log::error('Duitku Inquiry Failed', ['response' => $responseBody]);

            return [
                'success' => false,
                'message' => $responseBody['statusMessage'] ?? ($responseBody['Message'] ?? 'Merchant is not active / Channel not available'),
                'error' => $responseBody
            ];
        } catch (\Exception $e) {
            Log::error('Duitku Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Connection error: ' . $e->getMessage()
            ];
        }
    }

    public function verifyCallback($merchantCode, $amount, $merchantOrderId, $signature)
    {
        $calculatedSignature = md5($this->merchantCode . (int) $amount . $merchantOrderId . $this->apiKey);
        return $signature === $calculatedSignature;
    }

    /**
     * Check transaction status with Duitku
     */
    public function checkTransactionStatus($merchantOrderId)
    {
        $signature = md5($this->merchantCode . $merchantOrderId . $this->apiKey);

        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/inquiryStatus', [
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

            Log::error('Duitku Status Check Failed', [
                'url' => $this->baseUrl . '/inquiryStatus',
                'status' => $response->status(),
                'body' => $response->body()
            ]);

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
