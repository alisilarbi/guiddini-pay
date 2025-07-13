<?php

namespace App\Services\Payments\Gateways\Satim;

use App\Models\Transaction;
use App\Exceptions\PaymentException;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;
use App\Services\Payments\CredentialsService;
use App\Services\Payments\TransactionUpdater;
use Illuminate\Http\Client\ConnectionException;

class SatimInitiateService
{
    public function __construct(
        private CredentialsService $credentials,
        private TransactionUpdater $updater
    ) {}

    public function execute(Transaction $transaction): array
    {
        $params = [
            'userName' => $this->credentials->getFor($transaction, 'username'),
            'password' => $this->credentials->getFor($transaction, 'password'),
            'terminal_id' => $this->credentials->getFor($transaction, 'terminal'),
            'orderNumber' => $transaction->order_number,
            'amount' => (int)($transaction->amount * 100),
            'currency' => '012',
            'returnUrl' => route('payment.confirm', $transaction->order_number),
            'language' => 'FR',
            'jsonParams' => json_encode([
                "force_terminal_id" => $this->credentials->getFor($transaction, 'terminal'),
                "udf1" => $transaction->order_number,
                "udf5" => "00",
            ])
        ];

        $response = Http::timeout(30)
            ->withOptions(['verify' => false])
            ->get($this->baseUrl($transaction) . 'register.do', $params)
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
                    [
                        'current_environment' => $transaction->license_env,
                        'satim_response' => $response,
                    ],
                    '',
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
    }

    private function baseUrl(Transaction $transaction): string
    {
        return $transaction->license_env === 'production'
            ? 'https://cib.satim.dz/payment/rest/'
            : 'https://test.satim.dz/payment/rest/';
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
