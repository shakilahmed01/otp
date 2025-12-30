<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SslCommerzService
{
    protected $storeId;
    protected $storePassword;
    protected $sandboxMode;
    protected $baseUrl;

    public function __construct()
    {
        $this->storeId = config('services.sslcommerz.store_id');
        $this->storePassword = config('services.sslcommerz.store_password');
        $this->sandboxMode = config('services.sslcommerz.sandbox_mode', true);
        $this->baseUrl = $this->sandboxMode 
            ? 'https://sandbox.sslcommerz.com/gwprocess/v4/api.php'
            : 'https://securepay.sslcommerz.com/gwprocess/v4/api.php';
    }

    /**
     * Initialize payment with SSL Commerce
     */
    public function initPayment(array $paymentData): array
    {
        $postData = [
            'store_id' => $this->storeId,
            'store_passwd' => $this->storePassword,
            'total_amount' => $paymentData['total_amount'],
            'currency' => $paymentData['currency'] ?? 'BDT',
            'tran_id' => $paymentData['tran_id'],
            'success_url' => $paymentData['success_url'],
            'fail_url' => $paymentData['fail_url'],
            'cancel_url' => $paymentData['cancel_url'],
            'ipn_url' => $paymentData['ipn_url'],
            'cus_name' => $paymentData['cus_name'],
            'cus_email' => $paymentData['cus_email'],
            'cus_add1' => $paymentData['cus_address'] ?? '',
            'cus_city' => $paymentData['cus_city'] ?? '',
            'cus_postcode' => $paymentData['cus_postcode'] ?? '',
            'cus_country' => $paymentData['cus_country'] ?? 'Bangladesh',
            'cus_phone' => $paymentData['cus_phone'] ?? '',
            'product_name' => $paymentData['product_name'] ?? 'General',
            'product_category' => $paymentData['product_category'] ?? 'General',
            'product_profile' => $paymentData['product_profile'] ?? 'general',
            'shipping_method' => $paymentData['shipping_method'] ?? 'NO',
            'num_of_item' => $paymentData['num_of_item'] ?? 1,
            'multi_card_name' => $paymentData['multi_card_name'] ?? '',
            'value_a' => $paymentData['value_a'] ?? '',
            'value_b' => $paymentData['value_b'] ?? '',
            'value_c' => $paymentData['value_c'] ?? '',
            'value_d' => $paymentData['value_d'] ?? '',
        ];

        try {
            $response = Http::asForm()->post($this->baseUrl, $postData);

            if ($response->successful()) {
                // SSL Commerce API returns data in key=value format or JSON
                $responseBody = $response->body();
                
                // Parse the response
                $responseData = [];
                parse_str($responseBody, $responseData);
                
                // If parse_str doesn't work, try JSON
                if (empty($responseData) || !isset($responseData['status'])) {
                    $jsonData = $response->json();
                    if ($jsonData) {
                        $responseData = $jsonData;
                    }
                }
                
                if (isset($responseData['status']) && $responseData['status'] === 'SUCCESS') {
                    return [
                        'success' => true,
                        'gateway_page_url' => $responseData['GatewayPageURL'] ?? $responseData['gatewayPageURL'] ?? null,
                        'sessionkey' => $responseData['sessionkey'] ?? $responseData['sessionKey'] ?? null,
                        'data' => $responseData,
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => $responseData['failedreason'] ?? $responseData['failedReason'] ?? 'Payment initialization failed',
                        'data' => $responseData,
                    ];
                }
            }

            return [
                'success' => false,
                'message' => 'Failed to connect to payment gateway',
            ];
        } catch (\Exception $e) {
            Log::error('SSL Commerce Payment Init Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Payment gateway error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Validate payment from IPN (Instant Payment Notification)
     */
    public function validatePayment(array $requestData): array
    {
        $val_id = $requestData['val_id'] ?? null;
        
        if (!$val_id) {
            return [
                'success' => false,
                'message' => 'Validation ID not found',
            ];
        }

        $postData = [
            'val_id' => $val_id,
            'store_id' => $this->storeId,
            'store_passwd' => $this->storePassword,
            'format' => 'json',
        ];

        try {
            $url = $this->sandboxMode
                ? 'https://sandbox.sslcommerz.com/validator/api/validationserverAPI.php'
                : 'https://securepay.sslcommerz.com/validator/api/validationserverAPI.php';

            $response = Http::asForm()->post($url, $postData);

            if ($response->successful()) {
                $responseBody = $response->body();
                $responseData = $response->json();
                
                // If JSON parsing fails, try parsing as key=value format
                if (empty($responseData) || !isset($responseData['status'])) {
                    parse_str($responseBody, $responseData);
                }
                
                if (isset($responseData['status']) && ($responseData['status'] === 'VALID' || $responseData['status'] === 'VALIDATED')) {
                    return [
                        'success' => true,
                        'data' => $responseData,
                        'tran_id' => $responseData['tran_id'] ?? null,
                        'amount' => $responseData['amount'] ?? null,
                        'currency' => $responseData['currency'] ?? null,
                        'card_type' => $responseData['card_type'] ?? null,
                        'card_no' => $responseData['card_no'] ?? null,
                        'bank_tran_id' => $responseData['bank_tran_id'] ?? null,
                        'status' => $responseData['status'] ?? null,
                        'tran_date' => $responseData['tran_date'] ?? null,
                    ];
                } else {
                    return [
                        'success' => false,
                        'message' => $responseData['error'] ?? 'Payment validation failed',
                        'data' => $responseData,
                    ];
                }
            }

            return [
                'success' => false,
                'message' => 'Failed to validate payment',
            ];
        } catch (\Exception $e) {
            Log::error('SSL Commerce Payment Validation Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Payment validation error: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Verify transaction from callback data
     */
    public function verifyTransaction(array $requestData): bool
    {
        $tran_id = $requestData['tran_id'] ?? null;
        $amount = $requestData['amount'] ?? null;
        $currency = $requestData['currency'] ?? null;
        $status = $requestData['status'] ?? null;

        // Basic verification
        if ($status !== 'VALID') {
            return false;
        }

        // Verify hash
        $storeId = $requestData['store_id'] ?? '';
        $storePasswd = $this->storePassword;
        $tranId = $requestData['tran_id'] ?? '';
        $amount = $requestData['amount'] ?? '';
        $currency = $requestData['currency'] ?? '';
        $receivedHash = $requestData['verify_sign'] ?? '';
        
        if ($storeId !== $this->storeId) {
            return false;
        }

        // SSL Commerce verification logic
        // Note: In production, you should verify the verify_sign hash
        return true;
    }

    /**
     * Get payment URL
     */
    public function getPaymentUrl(): string
    {
        return $this->sandboxMode
            ? 'https://sandbox.sslcommerz.com/gwprocess/v4/api.php'
            : 'https://securepay.sslcommerz.com/gwprocess/v4/api.php';
    }
}

