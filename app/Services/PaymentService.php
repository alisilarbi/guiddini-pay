<?php

namespace App\Services;

use App\Models\Application;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    protected string $gatewayUrl = 'https://test.satim.dz/payment/rest/';
    protected int $orderIdStart = 592000;

    public function initiatePayment(array $data, string $appKey): array
    {
        return DB::transaction(function () use ($data, $appKey) {
            $application = Application::where('app_key', $appKey)->firstOrFail();
            $transaction = $this->createTransaction($data, $application);
            $response = $this->callPaymentGateway($transaction, $application);

            return [
                'transaction' => $transaction->fresh(),
                'gateway_response' => $response,
                'payment_url' => $response['formUrl'] ?? null
            ];
        });
    }

    protected function createTransaction(array $data, Application $application): Transaction
    {
        return Transaction::create([
            'pack_name' => $data['pack_name'],
            'price' => $data['price'],
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'client_order_id' => $this->generateClientOrderId(),
            'status' => 'initiated',
        ]);
    }

    protected function generateClientOrderId(): int
    {
        return Transaction::withTrashed()
            ->lockForUpdate()
            ->max('client_order_id') + 1 ?? $this->orderIdStart;
    }

    protected function callPaymentGateway(Transaction $transaction, Application $application): array
    {
        $params = [
            'userName' => $application->username,
            'password' => $application->password,
            'terminal_id' => $application->terminal,
            'orderNumber' => $transaction->client_order_id,
            'amount' => $transaction->price * 100,
            'currency' => '012',
            'returnUrl' => route('payment.confirm', $transaction->client_order_id),
            'failUrl' => route('payment.confirm', $transaction->client_order_id), // Both endpoints hit confirm
            'language' => 'EN',
            'jsonParams' => json_encode([
                "force_terminal_id" => $application->terminal,
                "udf1" => $transaction->client_order_id,
                "udf5" => "00",
            ])
        ];

        $response = Http::timeout(30)->get($this->gatewayUrl . 'register.do', $params);
        $responseData = $response->json();

        $this->updateInitialTransaction($transaction, $responseData);

        return $responseData;
    }

    protected function updateInitialTransaction(Transaction $transaction, array $response): void
    {
        $updateData = [
            'gateway_order_id' => $response['orderId'] ?? null,
            'gateway_code' => $response['errorCode'] ?? null,
            'gateway_error_code' => $response['errorCode'] ?? null,
            'gateway_response_message' => $response['errorMessage'] ?? null,
            'gateway_bool' => isset($response['errorCode']) && $response['errorCode'] == 0 ? 'true' : 'false',
            'status' => ($response['errorCode'] ?? null) == 0 ? 'pending_confirmation' : 'gateway_error'
        ];

        $transaction->update($updateData);
    }

    public function confirmPayment(string $clientOrderId, string $appKey): array
    {
        return DB::transaction(function () use ($clientOrderId, $appKey) {
            $transaction = Transaction::where('client_order_id', $clientOrderId)
                ->with('application')
                ->firstOrFail();

            $params = [
                'userName' => $transaction->application->username,
                'password' => $transaction->application->password,
                'orderId' => $transaction->gateway_order_id,
                'language' => 'EN',
            ];

            $response = Http::timeout(30)->get($this->gatewayUrl . 'confirmOrder.do', $params);
            $result = $response->json();

            $this->updateConfirmationDetails($transaction, $result);

            return [
                'status' => 'completed',
                'transaction' => $transaction->fresh(),
                'gateway_response' => $result
            ];
        });
    }

    protected function updateConfirmationDetails(Transaction $transaction, array $result): void
    {
        $updateData = [
            'gateway_code' => $result['actionCode'] ?? null,
            'gateway_error_code' => $result['errorCode'] ?? null,
            'gateway_response_message' => $result['errorMessage'] ?? $result['actionCodeDescription'] ?? null,
            'gateway_bool' => ($result['errorCode'] ?? 1) == 0 ? 'true' : 'false',
            'confirmation_response' => $result,
            'status' => $this->determineFinalStatus($result)
        ];

        if (isset($result['amount'])) {
            $updateData['price'] = $result['amount'] / 100;
        }

        $transaction->update($updateData);
    }

    protected function determineFinalStatus(array $result): string
    {
        return match ($result['errorCode'] ?? null) {
            0 => 'completed',
            2 => 'already_confirmed',
            6 => 'order_not_found',
            default => 'failed'
        };
    }
}
