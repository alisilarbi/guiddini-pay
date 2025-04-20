<?php
// app/Services/Payment/Gateway/ConfirmGatewayService.php

namespace App\Services\Api\Payments;

use App\Models\Transaction;
use App\Exceptions\PaymentException;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\ConnectionException;
use App\Services\Api\Payments\CredentialsService;
use App\Services\Api\Payments\TransactionUpdater;

class ConfirmGatewayService
{
    public function __construct(
        private CredentialsService $credentials,
        private TransactionUpdater $updater
    ) {}

    public function execute(Transaction $transaction): array
    {
        try {
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

            // if ($this->isErrorResponse($response)) {
            //     dd('error response');
            //     throw new PaymentException(
            //         $response['ErrorMessage'] ?? 'Confirmation failed',
            //         'CONFIRMATION_FAILED',
            //         402,
            //         ['gateway_response' => $response]
            //     );
            // }

            return $response;
        } catch (RequestException $e) {
            $this->updater->handleRequestError($transaction, $e);
            throw $this->mapRequestException($e);
        } catch (ConnectionException $e) {
            $this->updater->markUnreachable($transaction);
            throw new PaymentException('Gateway unavailable', 'GATEWAY_UNAVAILABLE', 503);
        }
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
