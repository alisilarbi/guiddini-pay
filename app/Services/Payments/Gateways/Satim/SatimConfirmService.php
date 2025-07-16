<?php

namespace App\Services\Payments\Gateways\Satim;

use App\Models\Transaction;
use Illuminate\Support\Facades\Http;
use App\Services\Payments\CredentialsService;
use App\Services\Payments\Gateways\Satim\SatimTransactionUpdater;

class SatimConfirmService
{
    public function __construct(
        private CredentialsService $credentials,
        private SatimTransactionUpdater $updater
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
            ->withOptions(['verify' => false])
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
}
