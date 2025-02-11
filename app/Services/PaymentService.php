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
    protected int $orderIdStart = 615000;

    public function initiatePayment(array $data, string $appKey): array
    {
        return DB::transaction(function () use ($data, $appKey) {
            $application = Application::where('app_key', $appKey)->firstOrFail();

            $transaction = $this->createTransaction($data, $application);
            $response = $this->callPaymentGateway($transaction, $application);

            return [
                'transaction' => $transaction,
                'gateway_response' => $response,
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
            'application_id' => $application->id,
        ]);
    }

    protected function generateClientOrderId(): int
    {
        $lastOrder = Transaction::lockForUpdate()->orderBy('client_order_id', 'desc')->first();
        return $lastOrder ? $lastOrder->client_order_id + 1 : $this->orderIdStart;
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
            'returnUrl' => route('payment.confirm', $transaction->client_order_id, $application->app_key),
            'failUrl' => route('payment.failed', $transaction->client_order_id),
            'language' => 'FR',
            'jsonParams' => json_encode([
                "force_terminal_id" => $application->terminal,
                "udf1" => $transaction->client_order_id,
                "udf5" => "00",
            ])
        ];

        $response = Http::timeout(30)->get($this->gatewayUrl . 'register.do', $params);

        if ($response->successful()) {
            $transaction->update([
                'gateway_order_id' => $response->json('orderId'),
                'status' => $response->json('errorCode') == 0 ? 'pending_confirmation' : 'gateway_error'
            ]);
        }

        return $response->json();
    }

    public function confirmPayment(string $clientOrderId, string $appKey = null): array
    {
        $transaction = Transaction::where('client_order_id', $clientOrderId)
            ->with('application')
            ->first();

        $params = [
            'userName' => $transaction->application->username,
            'password' => $transaction->application->password,
            'orderId' => $transaction->gateway_order_id,
            'language' => 'FR',
        ];

        try {
            $response = Http::timeout(30)->get($this->gatewayUrl . 'confirmOrder.do', $params);
            $result = $response->json();

            // dd($result);
            $this->updateTransactionStatus($transaction, $result);

            return [
                'status' => 'success',
                'transaction' => $transaction,
                'gateway_response' => $result
            ];
        } catch (\Exception $e) {
            Log::error("Confirmation failed: {$e->getMessage()}");
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    protected function updateTransactionStatus(Transaction $transaction, array $result): void
    {
        $updateData = ['confirmation_response' => $result];

        switch ($result['errorCode'] ?? null) {
            case 0:
                $updateData['status'] = $this->determineFinalStatus($result);
                break;
            case 2:
                $updateData['status'] = 'already_confirmed';
                break;
            default:
                $updateData['status'] = 'failed';
        }

        $transaction->update($updateData);

        dd($transaction);
    }

    protected function determineFinalStatus(array $result): string
    {
        if (($result['actionCode'] ?? 1) === 0 && ($result['OrderStatus'] ?? 0) === 2) {
            return 'completed';
        }
        return 'requires_verification';
    }
}
