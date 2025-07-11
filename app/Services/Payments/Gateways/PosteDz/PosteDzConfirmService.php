<?php

namespace App\Services\Payments\Gateways\PosteDz;

use App\Models\Transaction;
use Illuminate\Support\Facades\Http;
use App\Services\Payments\CredentialsService;
use App\Services\Payments\TransactionUpdater;

class PosteDzConfirmService
{
    public function __construct(
        private CredentialsService $credentials,
        private TransactionUpdater $updater
    ) {}

    /**
     * Execute the payment confirmation request to Poste DZ.
     *
     * @param Transaction $transaction The transaction to confirm
     * @return array The gateway response
     * @throws PaymentException If the request fails
     */
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

            return $response;
        } catch (\Exception $e) {
            $this->updater->handleRequestError($transaction, $e);
            throw $e;
        }
    }

    /**
     * Get the base URL for Poste DZ based on the environment.
     *
     * @param Transaction $transaction The transaction to determine the environment
     * @return string The base URL
     */
    private function baseUrl(Transaction $transaction): string
    {
        return $transaction->license_env === 'production'
            ? 'https://epay.poste.dz/payment/rest/'
            : 'https://webmarchand.poste.dz/payment/rest/'; // Adjust if test URL differs
    }
}
