<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

class RazorpayPaymentController extends Controller
{
    private Api $razorpayApi;

    public function __construct()
    {
        $keyId = env('RAZORPAY_KEY_ID', config('services.razorpay.key_id'));
        $keySecret = env('RAZORPAY_KEY_SECRET', config('services.razorpay.key_secret'));
        $this->razorpayApi = new Api($keyId, $keySecret);
    }

    /**
     * Create Razorpay Order
     *
     * POST /api/create-order
     * Request body: { amount (paise), currency, receipt, appointment_id (optional) }
     */
    public function createOrder(Request $request): JsonResponse
    {
        $request->validate([
            'amount' => 'required|integer|min:100',
            'currency' => 'nullable|string',
            'receipt' => 'nullable|string',
            'appointment_id' => 'nullable|integer|exists:appointments,id',
        ]);

        $amount = (int) $request->amount;
        $currency = $request->currency ?? 'INR';
        $receipt = $request->receipt ?? 'rcpt_' . time() . '_' . rand(100, 999);

        try {
            $orderData = [
                'receipt' => $receipt,
                'amount' => $amount,
                'currency' => $currency,
            ];

            \Illuminate\Support\Facades\Log::info('Creating Razorpay order payload:', $orderData);

            $razorpayOrder = $this->razorpayApi->order->create($orderData);


            // Save payment in database
            Payment::create([
                'customer_id' => $request->user()?->id,
                'appointment_id' => $request->appointment_id,
                'razorpay_order_id' => $razorpayOrder['id'],
                'amount' => $amount,
                'currency' => $currency,
                'status' => 'created',
            ]);

            return response()->json([
                'order_id' => $razorpayOrder['id'],
                'amount' => $razorpayOrder['amount'],
                'currency' => $razorpayOrder['currency'],
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to create Razorpay order',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Verify Payment Signature
     *
     * POST /api/verify-payment
     * Request body: { razorpay_order_id, razorpay_payment_id, razorpay_signature }
     */
    public function verifyPayment(Request $request): JsonResponse
    {
        $request->validate([
            'razorpay_order_id' => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature' => 'required|string',
        ]);

        $orderId = $request->razorpay_order_id;
        $paymentId = $request->razorpay_payment_id;
        $signature = $request->razorpay_signature;

        try {
            $attributes = [
                'razorpay_order_id' => $orderId,
                'razorpay_payment_id' => $paymentId,
                'razorpay_signature' => $signature,
            ];

            /** @var \Razorpay\Api\Utility $utility */
            $utility = $this->razorpayApi->utility;
            $utility->verifyPaymentSignature($attributes);


            // Update database payment record
            $payment = Payment::where('razorpay_order_id', $orderId)->first();
            if ($payment) {
                $payment->update([
                    'razorpay_payment_id' => $paymentId,
                    'razorpay_signature' => $signature,
                    'status' => 'paid',
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Payment verified successfully',
                'payment' => $payment,
            ], 200);

        } catch (SignatureVerificationError $e) {
            $payment = Payment::where('razorpay_order_id', $orderId)->first();
            if ($payment) {
                $payment->update([
                    'status' => 'failed',
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Signature verification failed',
            ], 400);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
