<?php

namespace App\Services\Payments\Gateways\PosteDz;

use App\Models\Transaction;
use App\Exceptions\PaymentException;
use Illuminate\Support\Facades\Http;
use App\Services\Payments\CredentialsService;
use App\Services\Payments\TransactionUpdater;

class PosteDzInitiateService
{
    public function __construct(
        private CredentialsService $credentials,
        private TransactionUpdater $updater
    ) {}

    /**
     * Execute the payment initiation request to Poste DZ.
     *
     * @param Transaction $transaction The transaction to initiate
     * @return array The gateway response
     * @throws PaymentException If the request fails
     */
    public function execute(Transaction $transaction): array
    {
        $params = [
            'userName' => $this->credentials->getFor($transaction, 'username'),
            'password' => $this->credentials->getFor($transaction, 'password'),
            'orderNumber' => $transaction->order_number,
            'amount' => (int)($transaction->amount * 100), // Convert to centimes
            'currency' => '012', // Algerian Dinar
            'returnUrl' => route('payment.confirm', $transaction->order_number),
            'language' => 'FR',
            'jsonParams' => json_encode([
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
            throw new PaymentException(
                $response['ErrorMessage'] ?? 'Payment gateway error',
                'GATEWAY_ERROR',
                402,
                ['gateway_response' => $response]
            );
        }

        return $response;

        try {
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

    /**
     * Check if the response indicates an error.
     *
     * @param array $response The gateway response
     * @return bool True if an error occurred, false otherwise
     */
    private function isErrorResponse(array $response): bool
    {
        return ($response['errorCode'] ?? '1') !== '0';
    }
}
