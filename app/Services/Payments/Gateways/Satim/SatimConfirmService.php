<?php

namespace App\Services\Payments\Gateways\Satim;

use App\Models\Transaction;
use App\Exceptions\PaymentException;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;
use App\Services\Payments\CredentialsService;
use App\Services\Payments\TransactionUpdater;
use Illuminate\Http\Client\ConnectionException;

class SatimConfirmService
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
            'orderId' => $transaction->order_id,
            'language' => 'FR',
        ];

        $response = Http::timeout(30)
            ->get($this->baseUrl($transaction) . 'confirmOrder.do', $params)
            ->throw()
            ->json();

        $this->updater->handleConfirmationResponse($transaction, $response);


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
