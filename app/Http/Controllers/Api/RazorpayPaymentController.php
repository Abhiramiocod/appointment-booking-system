<?php

namespace App\Http\Controllers\Api;

use App\actions\Payment\CreateRazorpayOrderAction;
use App\actions\Payment\VerifyRazorpayPaymentAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\CreateRazorpayOrderRequest;
use App\Http\Requests\Payment\VerifyRazorpayPaymentRequest;
use Exception;
use Illuminate\Http\JsonResponse;
use Razorpay\Api\Errors\SignatureVerificationError;

class RazorpayPaymentController extends Controller
{
    /**
     * Create Razorpay Order
     *
     * POST /api/create-order
     */
    public function createOrder(
        CreateRazorpayOrderRequest $request,
        CreateRazorpayOrderAction $action
    ): JsonResponse {
        try {
            $response = $action->execute(
                $request->validated(),
                $request->user()
            );

            return response()->json($response, 200);
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
     */
    public function verifyPayment(
        VerifyRazorpayPaymentRequest $request,
        VerifyRazorpayPaymentAction $action
    ): JsonResponse {
        try {
            $result = $action->execute($request->validated());

            return response()->json($result, 200);
        } catch (SignatureVerificationError $e) {
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
