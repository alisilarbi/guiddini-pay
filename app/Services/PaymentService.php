<?php

namespace App\Services;

use Exception;
use App\Models\Application;
use App\Models\Transaction;
use App\Exceptions\PaymentException;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PaymentService
{
    protected string $gatewayBaseUrl;

    public function initiatePayment(array $data, string $appKey): array
    {
        try {
            $application = Application::where('app_key', $appKey)->firstOrFail();
            $transaction = $this->createTransaction($data, $application);

            $this->setEnvironment($transaction);
            $response = $this->callPaymentGateway($transaction);

            $errorCode = $this->getGatewayErrorCode($response);

            if ($errorCode !== '0') {
                $this->updateTransactionStatus($transaction, $response);
                throw new PaymentException(
                    $response['ErrorMessage'] ?? $response['errorMessage'] ?? 'Payment gateway error',
                    'GATEWAY_ERROR',
                    402,
                    ['gateway_response' => $response]
                );
            }

            return [
                'formUrl' => $response['formUrl'],
                'transaction' => $transaction->only(['order_number', 'status', 'confirmation_status', 'amount', 'description'])
            ];
        } catch (ModelNotFoundException $e) {
            throw new PaymentException('Application not found', 'APP_NOT_FOUND', 404);
        } catch (ConnectionException $e) {
            throw new PaymentException('Payment gateway unreachable', 'GATEWAY_UNAVAILABLE', 503);
        } catch (RequestException $e) {
            throw new PaymentException(
                'Gateway request failed',
                'GATEWAY_ERROR',
                $e->response->status(),
                ['gateway_response' => $e->response->json()]
            );
        }
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

    private function setEnvironment(Transaction $transaction): void
    {
        $this->gatewayBaseUrl = $transaction->environment_type === 'production'
            ? 'https://cib.satim.dz/payment/rest/'
            : 'https://test.satim.dz/payment/rest/';
    }

    private function callPaymentGateway(Transaction $transaction): array
    {
        $params = [
            'userName' => $this->getCredentials($transaction, 'username'),
            'password' => $this->getCredentials($transaction, 'password'),
            'terminal_id' => $this->getCredentials($transaction, 'terminal'),
            'orderNumber' => $transaction->order_number,
            'amount' => (int)($transaction->amount * 100),
            'currency' => '012',
            'returnUrl' => route('payment.confirm', $transaction->order_number),
            // 'failUrl' => route('payment.failed', $transaction->order_number),
            'language' => 'FR',
            'jsonParams' => json_encode([
                "force_terminal_id" => $this->getCredentials($transaction, 'terminal'),
                "udf1" => $transaction->order_number,
                "udf5" => "00",
            ])
        ];

        return $this->performGatewayCall('register.do', $params, $transaction);
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

    private function updateTransactionStatus(Transaction $transaction, array $result): void
    {
        $updateData = [
            'confirmation_status' => $this->determineTransactionConfirmationStatus($result),
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

        dd($result);
        if (!isset($result['errorCode']) || $result['errorCode'] !== '0') {
            return 'gateway_error';
        }

        return 'completed';
    }

    private function determineTransactionConfirmationStatus(array $resut): string
    {
        if (!isset($result['actionCode']) || $result['actionCode'] !== '0') {
            return 'requires_verification';
        }

        return 'completed';
    }

    public function confirmPayment(string $orderNumber): array
    {
        try {
            $transaction = Transaction::where('order_number', $orderNumber)
                ->with('application')
                ->firstOrFail();

            $this->setEnvironment($transaction);
            $response = $this->callConfirmationGateway($transaction);

            $errorCode = $this->getGatewayErrorCode($response);
            $isSuccess = $errorCode === '0';

            $updateData = [
                'status' => $isSuccess ? 'paid' : 'failed',
                'confirmation_status' => $isSuccess ? 'confirmed' : 'rejected',
                'gateway_response' => json_encode($response)
            ];

            $transaction->update($updateData);

            return [
                'transaction' => $transaction,
                'gateway_response' => $response
            ];
        } catch (ModelNotFoundException $e) {
            throw new PaymentException('Transaction not found', 'TRANSACTION_NOT_FOUND', 404);
        } catch (ConnectionException $e) {
            throw new PaymentException('Payment gateway unreachable', 'GATEWAY_UNAVAILABLE', 503);
        } catch (RequestException $e) {
            throw new PaymentException(
                'Confirmation request failed',
                'CONFIRMATION_FAILED',
                $e->response->status(),
                ['gateway_response' => $e->response->json()]
            );
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

    private function getGatewayErrorCode(array $response): string
    {
        return (string)($response['ErrorCode'] ?? $response['errorCode'] ?? '1');
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
