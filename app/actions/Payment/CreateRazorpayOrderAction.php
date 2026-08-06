<?php

namespace App\actions\Payment;

use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Razorpay\Api\Api;

class CreateRazorpayOrderAction
{
    private Api $razorpayApi;

    public function __construct()
    {
        $keyId = env('RAZORPAY_KEY_ID', config('services.razorpay.key_id'));
        $keySecret = env('RAZORPAY_KEY_SECRET', config('services.razorpay.key_secret'));
        $this->razorpayApi = new Api($keyId, $keySecret);
    }

    public function execute(array $data, ?User $user = null): array
    {
        $amount = (int) $data['amount'];
        $currency = $data['currency'] ?? 'INR';
        $receipt = $data['receipt'] ?? 'rcpt_'.time().'_'.rand(100, 999);

        $orderData = [
            'receipt' => $receipt,
            'amount' => $amount,
            'currency' => $currency,
        ];

        Log::info('Creating Razorpay order payload:', $orderData);

        $razorpayOrder = $this->razorpayApi->order->create($orderData);

        Payment::create([
            'customer_id' => $user?->id,
            'appointment_id' => $data['appointment_id'] ?? null,
            'razorpay_order_id' => $razorpayOrder['id'],
            'amount' => $amount,
            'currency' => $currency,
            'status' => 'created',
        ]);

        return [
            'order_id' => $razorpayOrder['id'],
            'amount' => $razorpayOrder['amount'],
            'currency' => $razorpayOrder['currency'],
        ];
    }
}
