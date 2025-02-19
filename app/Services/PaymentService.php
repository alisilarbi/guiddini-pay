<?php

namespace App\Services;

use Exception;
use App\Models\Application;
use App\Models\Transaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\ConnectionException;
use SebastianBergmann\Invoker\TimeoutException;

class PaymentService
{
    protected string $gatewayUrl = 'https://test.satim.dz/payment/rest/';

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
            'orderNumber' => $transaction->order_number,
            'amount' => $transaction->amount * 100,
            'currency' => '012',
            'returnUrl' => route('payment.confirm', $transaction->order_number),
            'failUrl' => route('payment.failed', $transaction->order_number),
            'language' => 'FR',
            'jsonParams' => json_encode([
                "force_terminal_id" => $application->environment->satim_development_terminal,
                "udf1" => $transaction->order_number,
                "udf5" => "00",
            ])
        ];
        return $this->performGatewayCall('register.dos', $params, $transaction, function($result) use ($transaction) {
            $errorCode = $result['errorCode'] ?? null;
            $transaction->update([
                'order_id' => $result['orderId'] ?? null,
                'status' => ($errorCode == 0) ? 'pending_confirmation' : 'gateway_error'
            ]);
            return $result;
        });
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
        return $this->performGatewayCall('confirmOrder.do', $params, $transaction, function($result) use ($transaction) {
            $this->updateTransactionStatus($transaction, $result);
            return [
                'transaction' => $transaction,
                'gateway_response' => $result
            ];
        });
    }

    protected function performGatewayCall(string $endpoint, array $params, Transaction $transaction, callable $onSuccess): array
    {
        try {
            $response = Http::timeout(30)->get($this->gatewayUrl . $endpoint, $params);
            if ($response->successful()) {
                $result = $response->json();
                return $onSuccess($result);
            }
            $statusCode = $response->status();
            $errorMessage = $response->json('errorMessage', 'Unknown gateway error');


            if (in_array($statusCode, [401, 403])) {
                dd('we are here');
                $transaction->update(['status' => 'gateway_access_denied']);
                return [
                    'errorCode' => $statusCode,
                    'errorMessage' => 'Authentication failed',
                    'details' => $errorMessage
                ];
            }


            $transaction->update(['status' => 'gateway_failure']);
            return [
                'errorCode' => $statusCode,
                'errorMessage' => 'Payment gateway error',
                'details' => $errorMessage
            ];
        } catch (ConnectionException $e) {
            $transaction->update(['status' => 'gateway_unreachable']);
            return [
                'errorCode' => 'connection_error',
                'errorMessage' => 'Could not connect to payment gateway',
                'details' => $e->getMessage()
            ];
        } catch (TimeoutException $e) {
            $transaction->update(['status' => 'gateway_timeout']);
            return [
                'errorCode' => 'timeout',
                'errorMessage' => 'Gateway response timed out',
                'details' => $e->getMessage()
            ];
        } catch (RequestException $e) {
            $transaction->update(['status' => 'gateway_error']);
            return [
                'errorCode' => 'request_exception',
                'errorMessage' => 'Invalid gateway request',
                'details' => $e->getMessage()
            ];
        } catch (Exception $e) {
            $transaction->update(['status' => 'gateway_error']);
            return [
                'errorCode' => 'unexpected_error',
                'errorMessage' => 'Unexpected payment processing error',
                'details' => $e->getMessage()
            ];
        }
    }

    protected function updateTransactionStatus(Transaction $transaction, array $result): void
    {
        $errorCode = $result['ErrorCode'] ?? null;
        $updateData = [
            'card_holder_name' => $result['cardholderName'],
            'deposit_amount' => $result['depositAmount'],
            'currency' => $result['currency'],
            'auth_code' => $result['authCode'],
            'action_code' => $result['actionCode'],
            'action_code_description' => $result['actionCodeDescription'],
            'error_code' => $result['ErrorCode'],
            'error_message' => $result['ErrorMessage'],
        ];
        switch ((string) $errorCode) {
            case '0':
                $updateData['confirmation_status'] = $this->determineFinalStatus($result);
                break;
            case '2':
                $updateData['confirmation_status'] = 'already_confirmed';
                break;
            default:
                $updateData['confirmation_status'] = 'failed';
        }
        $transaction->update($updateData);
    }

    protected function determineFinalStatus(array $result): string
    {
        return (($result['actionCode'] ?? 1) === 0 && ($result['OrderStatus'] ?? 0) === 2)
            ? 'completed'
            : 'requires_verification';
    }
}
