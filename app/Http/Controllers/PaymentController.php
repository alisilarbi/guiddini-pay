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
            'amount' => 'required|numeric|min:50|decimal:0,2',
        ]);

        $result = $this->paymentService->initiatePayment(
            $validated,
            $request->header('X-App-Key')
        );

        return $result;
        return $this->formatResponse($result);
    }

    public function confirm(Request $request)
    {
        // $result = $this->paymentService->confirmPayment($request->orderId);
        // return $this->formatResponse($result);

        $result = $this->paymentService->confirmPayment($request->orderId);
        $response = $this->formatResponse($result);

        dd($response);
        $url = $result['transaction']->application->success_redirect_url;
        $queryString = http_build_query($response);

        return redirect()->to($url . '?' . $queryString);
    }

    public function failed(Request $request)
    {
        // $result = $this->paymentService->handleFailedPayment($request->orderId);
        // return $this->formatResponse($result);

        $result = $this->paymentService->confirmPayment($request->orderId);

        $response = $this->formatResponse($result);

        dd($response);

        $url = $result['transaction']->application->fail_redirect_url;

        $queryString = http_build_query($response);

        return redirect()->to($url . '?' . $queryString);
    }

    protected function formatResponse(array $result)
    {
        $success = $result['success'] ?? false;
        $statusCode = $success ? Response::HTTP_OK : Response::HTTP_BAD_REQUEST;

        $responseData = [
            'success' => $success,
            'code' => $result['code'] ?? ($success ? 'SUCCESS' : 'UNKNOWN_ERROR'),
            'message' => $result['message'] ?? ($success ? 'Operation successful' : 'An error occurred'),
            'data' => $result['data'] ?? null
        ];

        if (!$success) {
            $responseData['errors'] = $result['errors'] ?? null;
            $responseData['gateway_response'] = $result['gateway_response'] ?? null;
        }

        return response()->json($responseData, $statusCode);
    }
}
