<?php

namespace App\Http\Controllers;

use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PaymentController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function initiate(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:100',
        ]);

        $result = $this->paymentService->initiatePayment(
            $validated,
            $request->header('X-App-Key')
        );

        return $this->formatResponse($result);
    }

    public function confirm(Request $request)
    {
        $result = $this->paymentService->confirmPayment($request->orderId);
        return $this->formatResponse($result);
    }

    public function failed(Request $request)
    {
        $result = $this->paymentService->confirmPayment($request->orderId);
        return $this->formatResponse($result);
    }

    protected function formatResponse(array $result)
    {
        if (isset($result['gateway_response'])) {
            return (($result['gateway_response']['errorCode'] ?? null) == 0)
                ? response()->json($result, Response::HTTP_OK)
                : response()->json([
                    'error' => $result['gateway_response']['errorMessage'] ?? 'Payment error',
                    'gateway_response' => $result['gateway_response']
                ], Response::HTTP_BAD_REQUEST);
        }
        return ((isset($result['errorCode']) && $result['errorCode'] == 0))
            ? response()->json($result, Response::HTTP_OK)
            : response()->json($result, Response::HTTP_BAD_REQUEST);
    }
}
