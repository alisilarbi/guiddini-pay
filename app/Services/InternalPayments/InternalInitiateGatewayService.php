<?php

namespace App\Services\InternalPayments;

use App\Models\Transaction;
use App\Exceptions\PaymentException;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;
use App\Services\Payments\TransactionUpdater;
use Illuminate\Http\Client\ConnectionException;
use App\Services\InternalPayments\InternalTransactionUpdater;

class InternalInitiateGatewayService
{
    public function __construct(
        private InternalTransactionUpdater $updater
    ) {}

    public function execute(Transaction $transaction): array
    {
        $env = config('payments.env');
        $credentials = config('payments.credentials.' . $env);
        if (!$credentials || !isset($credentials['username'], $credentials['password'], $credentials['terminal'])) {
            throw new PaymentException("Missing credentials for $env environment", 'CONFIG_ERROR', 500);
        }

        try {
            $params = [
                'userName' => $credentials['username'],
                'password' => $credentials['password'],
                'terminal_id' => $credentials['terminal'],
                'orderNumber' => $transaction->order_number,
                'amount' => (int)($transaction->amount * 100),
                'currency' => '012',
                'returnUrl' => route('internal.payment.confirm', $transaction->order_number),
                'language' => 'FR',
                'jsonParams' => json_encode([
                    "force_terminal_id" => $credentials['terminal'],
                    "udf1" => $transaction->order_number,
                    "udf5" => "00",
                ])
            ];

            $baseUrl = $env === 'production'
                ? 'https://cib.satim.dz/payment/rest/'
                : 'https://test.satim.dz/payment/rest/';

            $response = Http::withoutVerifying()
                ->timeout(30)
                ->get($baseUrl . 'register.do', $params)
                ->throw()
                ->json();

            $this->updater->handleInitiationResponse($transaction, $response);
            if ($this->isErrorResponse($response)) {
                $errorCode = $response['ErrorCode'] ?? $response['errorCode'] ?? 'UNKNOWN';
                if ($errorCode === '5') {
                    throw new PaymentException(
                        'Access denied by the gateway',
                        'ACCESS_DENIED',
                        403,
                        ['current_environment' => $env, 'satim_response' => $response]
                    );
                }
                throw new PaymentException(
                    $response['ErrorMessage'] ?? 'Payment gateway error',
                    'GATEWAY_ERROR',
                    402,
                    ['gateway_response' => $response]
                );
            }

            return $response;
        } catch (RequestException $e) {
            $this->updater->handleRequestError($transaction, $e);
            throw $this->mapRequestException($e);
        } catch (ConnectionException $e) {
            $this->updater->markUnreachable($transaction);
            throw new PaymentException('Gateway unavailable', 'GATEWAY_UNAVAILABLE', 503);
        }
    }

    private function isErrorResponse(array $response): bool
    {
        return ($response['ErrorCode'] ?? $response['errorCode'] ?? '1') !== '0';
    }

    private function mapRequestException(RequestException $e): PaymentException
    {
        return new PaymentException(
            'Gateway request failed',
            'GATEWAY_ERROR',
            $e->response->status(),
            ['gateway_response' => $e->response->json()]
        );
    }
}
