<?php

namespace App\Services;

use Exception;
use App\Models\Application;
use App\Models\Transaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\ConnectionException;

class PaymentService
{
    protected string $gatewayBaseUrl;

    public function initiatePayment(array $data, string $appKey): array
    {
        try {
            $application = Application::where('app_key', $appKey)->firstOrFail();
            $transaction = $this->createTransaction($data, $application);

            $this->setEnvironment($transaction);
            $response = $this->callPaymentGateway($transaction, $application);

            return [
                'success' => ($response['errorCode'] ?? 1) === 0,
                'code' => 'PAYMENT_INITIATED',
                'message' => 'Payment initiated successfully',
                'data' => [
                    'formUrl' => $response['formUrl'] ?? null,
                    'transaction' => $transaction
                ],
                'gateway_response' => $response
            ];
        } catch (Exception $e) {
            return $this->handleException($e, 'initiate_payment_error');
        }
    }

    public function confirmPayment(string $orderId): array
    {
        try {
            $transaction = Transaction::where('order_id', $orderId)
                ->with('application')
                ->firstOrFail();

            $this->setEnvironment($transaction);
            $response = $this->callConfirmationGateway($transaction);

            return [
                'success' => ($response['errorCode'] ?? 1) === 0,
                'code' => 'PAYMENT_CONFIRMED',
                'message' => 'Payment confirmation processed',
                'data' => [
                    'transaction' => $transaction,
                    'gateway_response' => $response
                ]
            ];
        } catch (Exception $e) {
            return $this->handleException($e, 'confirmation_error');
        }
    }

    public function handleFailedPayment(string $orderId): array
    {
        try {
            $transaction = Transaction::where('order_id', $orderId)
                ->with('application')
                ->firstOrFail();

            $this->setEnvironment($transaction);
            $response = $this->callConfirmationGateway($transaction);

            return [
                'success' => false,
                'code' => 'PAYMENT_FAILED',
                'message' => 'Payment failure processed',
                'data' => [
                    'transaction' => $transaction,
                    'gateway_response' => $response
                ]
            ];
        } catch (Exception $e) {
            return $this->handleException($e, 'failure_handling_error');
        }
    }

    private function setEnvironment(Transaction $transaction): void
    {
        $this->gatewayBaseUrl = $transaction->environment_type === 'production'
            ? 'https://cib.satim.dz/payment/rest/'
            : 'https://test.satim.dz/payment/rest/';
    }

    private function callPaymentGateway(Transaction $transaction, Application $application): array
    {
        $params = [
            'userName' => $this->getCredentials($transaction, 'username'),
            'password' => $this->getCredentials($transaction, 'password'),
            'terminal_id' => $this->getCredentials($transaction, 'terminal'),
            'orderNumber' => $transaction->order_number,
            'amount' => (int)($transaction->amount * 100),
            'currency' => '012',
            'returnUrl' => route('payment.confirm', $transaction->order_number),
            'failUrl' => route('payment.failed', $transaction->order_number),
            'language' => 'FR',
            'jsonParams' => json_encode([
                "force_terminal_id" => $this->getCredentials($transaction, 'terminal'),
                "udf1" => $transaction->order_number,
                "udf5" => "00",
            ])
        ];

        return $this->performGatewayCall('register.do', $params, $transaction);
    }

    private function callConfirmationGateway(Transaction $transaction): array
    {
        $params = [
            'userName' => $this->getCredentials($transaction, 'username'),
            'password' => $this->getCredentials($transaction, 'password'),
            'orderId' => $transaction->order_id,
            'language' => 'FR',
        ];

        return $this->performGatewayCall('confirmOrder.do', $params, $transaction);
    }

    private function performGatewayCall(string $endpoint, array $params, Transaction $transaction): array
    {
        try {
            $response = Http::timeout(30)
                ->get($this->gatewayBaseUrl . $endpoint, $params);

            if (!$response->successful()) {
                throw new RequestException($response);
            }

            $result = $response->json();
            $this->updateTransactionStatus($transaction, $result);

            return $result;
        } catch (RequestException $e) {
            $transaction->update(['status' => 'gateway_error']);
            return [
                'errorCode' => $e->getCode(),
                'errorMessage' => 'Gateway request failed',
                'details' => $e->response->json()
            ];
        } catch (ConnectionException $e) {
            $transaction->update(['status' => 'gateway_unreachable']);
            return [
                'errorCode' => 'CONNECTION_ERROR',
                'errorMessage' => 'Could not connect to payment gateway'
            ];
        } catch (Exception $e) {
            $transaction->update(['status' => 'gateway_error']);
            return [
                'errorCode' => 'UNKNOWN_ERROR',
                'errorMessage' => 'Unexpected payment processing error'
            ];
        }
    }

    private function getCredentials(Transaction $transaction, string $type): string
    {
        $env = $transaction->application->environment;
        $prefix = $transaction->environment_type === 'production' ? 'satim_production' : 'satim_development';

        return match ($type) {
            'username' => $env->{$prefix . '_username'},
            'password' => $env->{$prefix . '_password'},
            'terminal' => $env->{$prefix . '_terminal'},
            default => throw new \InvalidArgumentException("Invalid credential type")
        };
    }

    private function createTransaction(array $data, Application $application): Transaction
    {
        return Transaction::create([
            'amount' => $data['amount'],
            'order_number' => $this->generateOrderNumber($application),
            'status' => 'initiated',
            'application_id' => $application->id,
            'environment_id' => $application->environment->id,
            'environment_type' => $application->environment_type
        ]);
    }

    private function updateTransactionStatus(Transaction $transaction, array $result): void
    {
        $updateData = [
            'status' => $this->determineTransactionStatus($result),
            'error_code' => $result['ErrorCode'] ?? null,
            'error_message' => $result['ErrorMessage'] ?? null,
            'gateway_response' => json_encode($result)
        ];

        if (isset($result['orderId'])) {
            $updateData['order_id'] = $result['orderId'];
        }

        $transaction->update($updateData);
    }

    private function determineTransactionStatus(array $result): string
    {
        if (($result['ErrorCode'] ?? 1) !== 0) return 'gateway_error';
        if (($result['actionCode'] ?? 1) !== 0) return 'requires_verification';
        return 'completed';
    }

    private function handleException(Exception $e, string $errorCode): array
    {
        return [
            'success' => false,
            'code' => $errorCode,
            'message' => 'Service unavailable. Please try again later.',
            'errors' => [
                'system' => config('app.debug') ? $e->getMessage() : null
            ]
        ];
    }

    private function generateOrderNumber(Application $application): string
    {
        $environmentId = $application->environment->id;

        do {
            $unique = uniqid(mt_rand(), true);
            $orderNumber = strtoupper(substr(base_convert($unique, 16, 36), 0, 20));
        } while (
            Transaction::where('order_number', $orderNumber)
            ->where('environment_id', $environmentId)
            ->exists()
        );

        return $orderNumber;
    }
}
