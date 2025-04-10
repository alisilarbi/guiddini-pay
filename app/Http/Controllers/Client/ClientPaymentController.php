<?php

namespace App\Http\Controllers\Client;

use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\HandlesApiExceptions;
use App\Services\Payments\PaymentService;
use App\Http\Resources\API\PaymentResource;
use App\Http\Resources\API\TransactionResource;

class ClientPaymentController extends Controller
{
    use HandlesApiExceptions;

    public function __construct(private PaymentService $paymentService) {}

    public function initiate(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:50|decimal:0,2',
        ]);

        $result = $this->paymentService->initiatePayment(
            $validated,
            $request->header('X-App-Key')
        );

        try {
            $validated = $request->validate([
                'amount' => 'required|numeric|min:50|decimal:0,2',
            ]);

            $result = $this->paymentService->initiatePayment(
                $validated,
                $request->header('X-App-Key')
            );

            return new PaymentResource([
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

            $redirectUrl = $transaction->application->redirect_url;

            $queryParams = http_build_query([
                'order_number' => $orderNumber,
                // 'status' => $transaction->status,
                // 'confirmation_status' => $transaction->confirmation_status,
                // 'gateway_code' => $this->getGatewayErrorCode($gatewayResponse)
            ]);

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

            $request->validate([
                'order_number' => 'required',
            ]);

            $transaction = Transaction::where('order_number', $request->order_number)->first();


            return new TransactionResource([
                'success' => true,
                'code' => 'TRANSACTION_FOUND',
                'message' => 'Transaction retrieved successfully',
                'data' => [
                    'transaction' => $transaction->toArray(),
                    'formUrl' => null,
                ],
                'http_code' => 200
            ]);
        } catch (\Throwable $e) {
            return $this->handleApiException($e);
        }
    }
}
