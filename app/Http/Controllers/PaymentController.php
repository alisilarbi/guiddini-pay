<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Traits\HandlesApiExceptions;
use App\Services\Payments\PaymentService;
use App\Http\Resources\ApiResponseResource;

class PaymentController extends Controller
{
    use HandlesApiExceptions;

    public function __construct(private PaymentService $paymentService) {}

    public function initiate(Request $request)
    {
        try {
            $validated = $request->validate([
                'amount' => 'required|numeric|min:50|decimal:0,2',
            ]);

            $result = $this->paymentService->initiatePayment(
                $validated,
                $request->header('X-App-Key')
            );

            return new ApiResponseResource([
                'success' => true,
                'code' => 'PAYMENT_INITIATED',
                'message' => 'Payment initiated successfully',
                'data' => $result,
                'http_code' => 201
            ]);
        } catch (\Throwable $e) {
            return $this->handleApiException($e);
        }
    }

    public function confirm(string $orderNumber)
    {
        try {
            $result = $this->paymentService->confirmPayment($orderNumber);

            $transaction = $result['transaction'];
            $gatewayResponse = $result['gateway_response'];

            $redirectUrl = $transaction->status === 'paid'
                ? $transaction->application->success_redirect_url
                : $transaction->application->fail_redirect_url;

            $queryParams = http_build_query([
                'status' => $transaction->status,
                'confirmation_status' => $transaction->confirmation_status,
                'order_number' => $orderNumber,
                'gateway_code' => $this->getGatewayErrorCode($gatewayResponse)
            ]);
            dd("$redirectUrl?$queryParams");

            return redirect()->to("$redirectUrl?$queryParams");
        } catch (\Throwable $e) {
            return $this->handleApiException($e);
        }
    }

    private function getGatewayErrorCode(array $response): string
    {
        return (string)($response['ErrorCode'] ?? $response['errorCode'] ?? 'UNKNOWN');
    }

    public function getTransaction(Request $request)
    {
        try {
            $transaction = Transaction::findOrFail($request->input('transaction_id'));

            return new ApiResponseResource([
                'success' => true,
                'code' => 'TRANSACTION_FOUND',
                'message' => 'Transaction retrieved successfully',
                'data' => $transaction,
                'http_code' => 200
            ]);
        } catch (\Throwable $e) {
            return $this->handleApiException($e);
        }
    }
}
