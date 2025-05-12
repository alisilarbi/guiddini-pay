<?php

namespace App\Services\InternalPayments;

use App\Models\Transaction;
use App\Exceptions\PaymentException;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;
use App\Services\Payments\TransactionUpdater;
use Illuminate\Http\Client\ConnectionException;
use App\Services\InternalPayments\InternalTransactionUpdater;

class InternalConfirmGatewayService
{
    public function __construct(
        private InternalTransactionUpdater $updater
    ) {}

    public function execute(Transaction $transaction): array
    {
        $env = config('payments.env');
        $credentials = config('payments.credentials.' . $env);
        if (!$credentials || !isset($credentials['username'], $credentials['password'])) {
            throw new PaymentException("Missing credentials for $env environment", 'CONFIG_ERROR', 500);
        }

        try {
            $params = [
                'userName' => $credentials['username'],
                'password' => $credentials['password'],
                'orderId' => $transaction->order_id,
                'language' => 'FR',
            ];

            $baseUrl = $env === 'production'
                ? 'https://cib.satim.dz/payment/rest/'
                : 'https://test.satim.dz/payment/rest/';

            $response = Http::withoutVerifying()
                ->timeout(30)
                ->get($baseUrl . 'confirmOrder.do', $params)
                ->throw()
                ->json();

            $this->updater->handleConfirmationResponse($transaction, $response);

            return $response;
        } catch (RequestException $e) {
            $this->updater->handleRequestError($transaction, $e);
            throw $this->mapRequestException($e);
        } catch (ConnectionException $e) {
            $this->updater->markUnreachable($transaction);
            throw new PaymentException('Gateway unavailable', 'GATEWAY_UNAVAILABLE', 503);
        }
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