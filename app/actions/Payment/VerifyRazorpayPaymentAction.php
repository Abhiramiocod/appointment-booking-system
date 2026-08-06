<?php

namespace App\actions\Payment;

use App\Models\Payment;
use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;
use Razorpay\Api\Utility;

class VerifyRazorpayPaymentAction
{
    private Api $razorpayApi;

    public function __construct()
    {
        $keyId = env('RAZORPAY_KEY_ID', config('services.razorpay.key_id'));
        $keySecret = env('RAZORPAY_KEY_SECRET', config('services.razorpay.key_secret'));
        $this->razorpayApi = new Api($keyId, $keySecret);
    }

    public function execute(array $data): array
    {
        $orderId = $data['razorpay_order_id'];
        $paymentId = $data['razorpay_payment_id'];
        $signature = $data['razorpay_signature'];

        try {
            $attributes = [
                'razorpay_order_id' => $orderId,
                'razorpay_payment_id' => $paymentId,
                'razorpay_signature' => $signature,
            ];

            /** @var Utility $utility */
            $utility = $this->razorpayApi->utility;
            $utility->verifyPaymentSignature($attributes);

            $payment = Payment::where('razorpay_order_id', $orderId)->first();
            if ($payment) {
                $payment->update([
                    'razorpay_payment_id' => $paymentId,
                    'razorpay_signature' => $signature,
                    'status' => 'paid',
                ]);
            }

            return [
                'status' => 'success',
                'message' => 'Payment verified successfully',
                'payment' => $payment,
            ];

        } catch (SignatureVerificationError $e) {
            $payment = Payment::where('razorpay_order_id', $orderId)->first();
            if ($payment) {
                $payment->update([
                    'status' => 'failed',
                ]);
            }

            throw $e;
        }
    }
}
