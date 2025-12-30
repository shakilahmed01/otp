<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\SslCommerzService;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected $sslCommerzService;

    public function __construct(SslCommerzService $sslCommerzService)
    {
        $this->sslCommerzService = $sslCommerzService;
        // Web routes middleware - API routes use auth:sanctum in routes file
        $this->middleware('auth')->except(['success', 'fail', 'cancel', 'ipn', 'apiInitPayment', 'apiShow', 'apiIndex', 'apiStatus']);
    }

    /**
     * Initialize payment
     */
    public function initPayment(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'currency' => 'nullable|string|max:10',
            'product_name' => 'nullable|string|max:255',
            'product_category' => 'nullable|string|max:255',
            'cus_name' => 'nullable|string|max:255',
            'cus_email' => 'nullable|email|max:255',
            'cus_phone' => 'nullable|string|max:20',
            'cus_address' => 'nullable|string|max:500',
            'cus_city' => 'nullable|string|max:100',
            'cus_country' => 'nullable|string|max:100',
            'cus_postcode' => 'nullable|string|max:20',
        ]);

        $user = Auth::user();

        // Generate unique transaction ID
        $tranId = 'TXN' . Str::upper(Str::random(10)) . time();

        // Create payment record
        $payment = Payment::create([
            'user_id' => $user->id,
            'tran_id' => $tranId,
            'amount' => $request->amount,
            'currency' => $request->currency ?? 'BDT',
            'status' => 'pending',
            'product_name' => $request->product_name ?? 'General Payment',
            'product_category' => $request->product_category ?? 'General',
            'cus_name' => $request->cus_name ?? $user->name,
            'cus_email' => $request->cus_email ?? $user->email,
            'cus_phone' => $request->cus_phone ?? '',
            'cus_address' => $request->cus_address ?? '',
            'cus_city' => $request->cus_city ?? '',
            'cus_country' => $request->cus_country ?? 'Bangladesh',
            'cus_postcode' => $request->cus_postcode ?? '',
            'success_url' => route('payment.success'),
            'fail_url' => route('payment.fail'),
            'cancel_url' => route('payment.cancel'),
            'ipn_url' => route('payment.ipn'),
        ]);

        // Prepare payment data for SSL Commerce
        $paymentData = [
            'total_amount' => $request->amount,
            'currency' => $request->currency ?? 'BDT',
            'tran_id' => $tranId,
            'success_url' => route('payment.success'),
            'fail_url' => route('payment.fail'),
            'cancel_url' => route('payment.cancel'),
            'ipn_url' => route('payment.ipn'),
            'cus_name' => $request->cus_name ?? $user->name,
            'cus_email' => $request->cus_email ?? $user->email,
            'cus_phone' => $request->cus_phone ?? '',
            'cus_address' => $request->cus_address ?? '',
            'cus_city' => $request->cus_city ?? '',
            'cus_country' => $request->cus_country ?? 'Bangladesh',
            'cus_postcode' => $request->cus_postcode ?? '',
            'product_name' => $request->product_name ?? 'General Payment',
            'product_category' => $request->product_category ?? 'General',
            'product_profile' => 'general',
            'shipping_method' => 'NO',
            'num_of_item' => 1,
        ];

        // Initialize payment with SSL Commerce
        $result = $this->sslCommerzService->initPayment($paymentData);

        if ($result['success']) {
            // Update payment with SSL Commerce response
            $payment->update([
                'ssl_response' => $result['data'],
            ]);

            // Redirect to payment gateway
            return redirect($result['gateway_page_url']);
        }

        // Update payment status to failed
        $payment->update([
            'status' => 'failed',
            'ssl_response' => $result,
        ]);

        return back()->with('error', $result['message'] ?? 'Payment initialization failed. Please try again.');
    }

    /**
     * Handle successful payment callback
     */
    public function success(Request $request)
    {
        $tranId = $request->tran_id;
        $payment = Payment::where('tran_id', $tranId)->first();

        if (!$payment) {
            return redirect()->route('home')->with('error', 'Payment not found.');
        }

        // Validate payment
        $validation = $this->sslCommerzService->validatePayment($request->all());

        if ($validation['success']) {
            // Update payment record
            $payment->update([
                'status' => 'success',
                'payment_method' => $validation['data']['card_brand'] ?? null,
                'bank_tran_id' => $validation['bank_tran_id'] ?? $request->bank_tran_id,
                'card_type' => $validation['card_type'] ?? null,
                'card_no' => $validation['card_no'] ?? null,
                'ssl_response' => array_merge($payment->ssl_response ?? [], $validation['data']),
                'paid_at' => now(),
            ]);

            return redirect()->route('payment.show', $payment->id)
                ->with('success', 'Payment completed successfully!');
        }

        // If validation fails, still show pending status
        return redirect()->route('payment.show', $payment->id)
            ->with('warning', 'Payment received but validation pending. Please wait for confirmation.');
    }

    /**
     * Handle failed payment callback
     */
    public function fail(Request $request)
    {
        $tranId = $request->tran_id;
        $payment = Payment::where('tran_id', $tranId)->first();

        if ($payment) {
            $payment->update([
                'status' => 'failed',
                'ssl_response' => array_merge($payment->ssl_response ?? [], $request->all()),
            ]);
        }

        return redirect()->route('home')
            ->with('error', 'Payment failed. Please try again.');
    }

    /**
     * Handle cancelled payment callback
     */
    public function cancel(Request $request)
    {
        $tranId = $request->tran_id;
        $payment = Payment::where('tran_id', $tranId)->first();

        if ($payment) {
            $payment->update([
                'status' => 'cancelled',
                'ssl_response' => array_merge($payment->ssl_response ?? [], $request->all()),
            ]);
        }

        return redirect()->route('home')
            ->with('warning', 'Payment was cancelled.');
    }

    /**
     * Handle IPN (Instant Payment Notification)
     */
    public function ipn(Request $request)
    {
        Log::info('SSL Commerce IPN Received', $request->all());

        $tranId = $request->tran_id;
        $payment = Payment::where('tran_id', $tranId)->first();

        if (!$payment) {
            return response()->json(['error' => 'Payment not found'], 404);
        }

        // Validate payment
        $validation = $this->sslCommerzService->validatePayment($request->all());

        if ($validation['success'] && $request->status === 'VALID') {
            $payment->update([
                'status' => 'success',
                'payment_method' => $validation['data']['card_brand'] ?? null,
                'bank_tran_id' => $validation['bank_tran_id'] ?? $request->bank_tran_id,
                'card_type' => $validation['card_type'] ?? null,
                'card_no' => $validation['card_no'] ?? null,
                'ssl_response' => array_merge($payment->ssl_response ?? [], $validation['data']),
                'paid_at' => now(),
            ]);

            return response()->json(['status' => 'success', 'message' => 'Payment validated']);
        }

        // If validation fails or status is not VALID
        $payment->update([
            'status' => 'failed',
            'ssl_response' => array_merge($payment->ssl_response ?? [], $request->all()),
        ]);

        return response()->json(['status' => 'failed', 'message' => 'Payment validation failed']);
    }

    /**
     * Show payment details
     */
    public function show($id)
    {
        $payment = Payment::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return view('payment.show', compact('payment'));
    }

    /**
     * Show payment form
     */
    public function create()
    {
        return view('payment.create');
    }

    /**
     * List user's payments
     */
    public function index()
    {
        $payments = Payment::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('payment.index', compact('payments'));
    }

    // ==================== API METHODS ====================

    /**
     * Initialize payment via API
     */
    public function apiInitPayment(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'currency' => 'nullable|string|max:10',
            'product_name' => 'nullable|string|max:255',
            'product_category' => 'nullable|string|max:255',
            'cus_name' => 'nullable|string|max:255',
            'cus_email' => 'nullable|email|max:255',
            'cus_phone' => 'nullable|string|max:20',
            'cus_address' => 'nullable|string|max:500',
            'cus_city' => 'nullable|string|max:100',
            'cus_country' => 'nullable|string|max:100',
            'cus_postcode' => 'nullable|string|max:20',
            'success_url' => 'nullable|url',
            'fail_url' => 'nullable|url',
            'cancel_url' => 'nullable|url',
        ]);

        $user = $request->user();

        // Generate unique transaction ID
        $tranId = 'TXN' . Str::upper(Str::random(10)) . time();

        // Use provided URLs or default to web routes
        $successUrl = $request->success_url ?? route('payment.success');
        $failUrl = $request->fail_url ?? route('payment.fail');
        $cancelUrl = $request->cancel_url ?? route('payment.cancel');
        $ipnUrl = route('payment.ipn');

        // Create payment record
        $payment = Payment::create([
            'user_id' => $user->id,
            'tran_id' => $tranId,
            'amount' => $request->amount,
            'currency' => $request->currency ?? 'BDT',
            'status' => 'pending',
            'product_name' => $request->product_name ?? 'General Payment',
            'product_category' => $request->product_category ?? 'General',
            'cus_name' => $request->cus_name ?? $user->name,
            'cus_email' => $request->cus_email ?? $user->email,
            'cus_phone' => $request->cus_phone ?? '',
            'cus_address' => $request->cus_address ?? '',
            'cus_city' => $request->cus_city ?? '',
            'cus_country' => $request->cus_country ?? 'Bangladesh',
            'cus_postcode' => $request->cus_postcode ?? '',
            'success_url' => $successUrl,
            'fail_url' => $failUrl,
            'cancel_url' => $cancelUrl,
            'ipn_url' => $ipnUrl,
        ]);

        // Prepare payment data for SSL Commerce
        $paymentData = [
            'total_amount' => $request->amount,
            'currency' => $request->currency ?? 'BDT',
            'tran_id' => $tranId,
            'success_url' => $successUrl,
            'fail_url' => $failUrl,
            'cancel_url' => $cancelUrl,
            'ipn_url' => $ipnUrl,
            'cus_name' => $request->cus_name ?? $user->name,
            'cus_email' => $request->cus_email ?? $user->email,
            'cus_phone' => $request->cus_phone ?? '',
            'cus_address' => $request->cus_address ?? '',
            'cus_city' => $request->cus_city ?? '',
            'cus_country' => $request->cus_country ?? 'Bangladesh',
            'cus_postcode' => $request->cus_postcode ?? '',
            'product_name' => $request->product_name ?? 'General Payment',
            'product_category' => $request->product_category ?? 'General',
            'product_profile' => 'general',
            'shipping_method' => 'NO',
            'num_of_item' => 1,
        ];

        // Initialize payment with SSL Commerce
        $result = $this->sslCommerzService->initPayment($paymentData);

        if ($result['success']) {
            // Update payment with SSL Commerce response
            $payment->update([
                'ssl_response' => $result['data'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment initialized successfully',
                'data' => [
                    'payment_id' => $payment->id,
                    'tran_id' => $payment->tran_id,
                    'amount' => $payment->amount,
                    'currency' => $payment->currency,
                    'status' => $payment->status,
                    'gateway_url' => $result['gateway_page_url'],
                    'sessionkey' => $result['sessionkey'] ?? null,
                ],
            ], 201);
        }

        // Update payment status to failed
        $payment->update([
            'status' => 'failed',
            'ssl_response' => $result,
        ]);

        return response()->json([
            'success' => false,
            'message' => $result['message'] ?? 'Payment initialization failed',
            'data' => [
                'payment_id' => $payment->id,
                'tran_id' => $payment->tran_id,
            ],
        ], 400);
    }

    /**
     * Get payment details via API
     */
    public function apiShow($id)
    {
        $payment = Payment::where('id', $id)
            ->where('user_id', request()->user()->id)
            ->first();

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $payment->id,
                'tran_id' => $payment->tran_id,
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'status' => $payment->status,
                'payment_method' => $payment->payment_method,
                'bank_tran_id' => $payment->bank_tran_id,
                'card_type' => $payment->card_type,
                'product_name' => $payment->product_name,
                'product_category' => $payment->product_category,
                'customer' => [
                    'name' => $payment->cus_name,
                    'email' => $payment->cus_email,
                    'phone' => $payment->cus_phone,
                    'address' => $payment->cus_address,
                    'city' => $payment->cus_city,
                    'country' => $payment->cus_country,
                    'postcode' => $payment->cus_postcode,
                ],
                'created_at' => $payment->created_at->toISOString(),
                'paid_at' => $payment->paid_at ? $payment->paid_at->toISOString() : null,
            ],
        ]);
    }

    /**
     * List user's payments via API
     */
    public function apiIndex(Request $request)
    {
        $perPage = $request->get('per_page', 15);
        $payments = Payment::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $payments->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'tran_id' => $payment->tran_id,
                    'amount' => $payment->amount,
                    'currency' => $payment->currency,
                    'status' => $payment->status,
                    'product_name' => $payment->product_name,
                    'created_at' => $payment->created_at->toISOString(),
                    'paid_at' => $payment->paid_at ? $payment->paid_at->toISOString() : null,
                ];
            }),
            'meta' => [
                'current_page' => $payments->currentPage(),
                'last_page' => $payments->lastPage(),
                'per_page' => $payments->perPage(),
                'total' => $payments->total(),
            ],
        ]);
    }

    /**
     * Check payment status via API
     */
    public function apiStatus($tranId)
    {
        $payment = Payment::where('tran_id', $tranId)
            ->where('user_id', request()->user()->id)
            ->first();

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'tran_id' => $payment->tran_id,
                'status' => $payment->status,
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'is_successful' => $payment->isSuccessful(),
                'is_pending' => $payment->isPending(),
                'is_failed' => $payment->isFailed(),
                'paid_at' => $payment->paid_at ? $payment->paid_at->toISOString() : null,
            ],
        ]);
    }
}
