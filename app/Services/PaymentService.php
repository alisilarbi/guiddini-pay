<?php

namespace App\Services;

use App\Models\Application;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class PaymentService
{
    protected string $gatewayUrl = 'https://test.satim.dz/payment/rest/';
    protected int $orderIdStart = 6137000;

    public function initiatePayment(array $data, string $appKey): array
    {

        $application = Application::where('app_key', $appKey)->first();

        $transaction = $this->createTransaction($data, $application);
        $response = $this->callPaymentGateway($transaction, $application);

        return [
            'transaction' => $transaction,
            'gateway_response' => $response,
        ];
    }

    protected function generateOrderNumber(Application $application): string
    {
        $environmentId = $application->environment->id;

        do {
            $uniqidValue = uniqid(mt_rand(), true);

            $base36Value = base_convert($uniqidValue, 16, 36);

            $shortValue = substr($base36Value, 0, 20);

            $orderNumber = strtoupper($shortValue);

        } while (
            Transaction::where('order_number', $orderNumber)->where('environment_id', $environmentId)->exists()
        );

        return $orderNumber;
    }

    protected function createTransaction(array $data, Application $application): Transaction
    {
        return Transaction::create([
            'amount' => $data['amount'],
            'order_number' => $this->generateOrderNumber($application),
            'status' => 'initiated',
            'application_id' => $application->id,
            'environment_id' => $application->environment->id,
        ]);
    }

    protected function callPaymentGateway(Transaction $transaction, Application $application): array
    {
        $params = [
            'userName' => $application->environment->satim_development_username,
            'password' => $application->environment->satim_development_password,
            'terminal_id' => $application->environment->satim_development_terminal,
            'orderNumber' => Str::random(5),
            'amount' => $transaction->amount * 100,
            'currency' => '012',
            'returnUrl' => route('payment.confirm', $transaction->order_number),
            'failUrl' => route('payment.failed', $transaction->order_number),
            'language' => 'FR',
            'jsonParams' => json_encode([
                "force_terminal_id" => $application->environment->satim_development_terminal,
                "udf1" => $transaction->client_order_id,
                "udf5" => "00",
            ])
        ];

        $response = Http::timeout(30)->get($this->gatewayUrl . 'register.do', $params);
        if ($response->successful()) {
            $transaction->update([
                'order_id' => $response->json('orderId'),
                'status' => $response->json('errorCode') == 0 ? 'pending_confirmation' : 'gateway_error'
            ]);
        }

        return $response->json();
    }

    public function confirmPayment(string $order_id): array
    {

        $transaction = Transaction::where('order_id', $order_id)
            ->with('application')
            ->first();

        $params = [
            'userName' => $transaction->application->environment->satim_development_username,
            'password' => $transaction->application->environment->satim_development_password,
            'orderId' => $transaction->order_id,
            'language' => 'FR',
        ];

        $response = Http::timeout(30)->get($this->gatewayUrl . 'confirmOrder.do', $params);
        $result = $response->json();

        $this->updateTransactionStatus($transaction, $result);
        dd([
            'status' => 'success',
            'transaction' => $transaction,
            'gateway_response' => $result
        ]);

        return [
            'status' => 'success',
            'transaction' => $transaction,
            'gateway_response' => $result
        ];
    }

    protected function updateTransactionStatus(Transaction $transaction, array $result): void
    {

        dd('service update transaction');
        $errorCode = $result['ErrorCode'] ?? null;
        $updateData = [
            'confirmation_response' => $result,
            'gateway_order_id' => $result['OrderNumber'] ?? null,
            'gateway_confirmation_status' => $result['actionCode'] ?? null,
            'gateway_response_message' => $result['actionCodeDescription'] ?? null,
            'gateway_error_code' => $result['ErrorCode'] ?? null,
            'gateway_code' => $result['authCode'] ?? null,
            'amount' => $result['Amount'] / 100 ?? null,
            'currency' => $result['currency'] ?? null,
        ];

        switch ((string) $errorCode) {
            case '0':
                $updateData['gateway_confirmation_status'] = $this->determineFinalStatus($result);
                break;
            case '2':
                $updateData['gateway_confirmation_status'] = 'already_confirmed';
                break;
            default:
                $updateData['gateway_confirmation_status'] = 'failed';
        }

        $transaction->update($updateData);
    }

    protected function determineFinalStatus(array $result): string
    {
        dd('service determine final status');
        if (($result['actionCode'] ?? 1) === 0 && ($result['OrderStatus'] ?? 0) === 2) {
            return 'completed';
        }
        return 'requires_verification';
    }


}
