<?php

namespace App\Services\Payments;

use App\Models\Application;
use App\Models\Transaction;
use Illuminate\Support\Facades\URL;
use App\Exceptions\PaymentException;
use App\Services\Payments\CredentialsService;
use App\Services\Payments\TransactionUpdater;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Services\Payments\Gateways\Satim\SatimConfirmService;
use App\Services\Payments\Gateways\Satim\SatimInitiateService;
use App\Services\Payments\Gateways\PosteDz\PosteDzConfirmService;
use App\Services\Payments\Gateways\PosteDz\PosteDzInitiateService;

class PaymentService
{
    public function __construct(
        private CredentialsService $credentials
    ) {}


    /**
     * Initiate a payment transaction.
     *
     * @param array $data Payment data including amount and origin
     * @param string $appKey The application key
     * @return array Response including form URL and transaction details
     * @throws PaymentException If initiation fails
     */
    public function initiatePayment(array $data, string $appKey): array
    {
        $application = Application::where('app_key', $appKey)->firstOrFail();
        $transaction = $this->createTransaction($data, $application);

        $gatewayType = $transaction->application->license->gateway_type;
        $initiateService = $this->getInitiateService($gatewayType);
        $response = $initiateService->execute($transaction);

        return [
            'formUrl' => $response['formUrl'],
            'transaction' => $transaction->only(['order_number', 'status', 'amount'])
        ];
    }


    /**
     * Confirm a payment transaction.
     *
     * @param string $orderNumber The transaction order number
     * @return array Confirmation result including transaction and gateway response
     * @throws PaymentException If confirmation fails
     */
    public function confirmPayment(string $orderNumber): array
    {
        $transaction = Transaction::with('application')
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        $gatewayType = $transaction->application->license->gateway_type;
        $confirmService = $this->getConfirmService($gatewayType);
        $response = $confirmService->execute($transaction);

        return [
            'transaction' => $transaction,
            'gateway_response' => $response
        ];
    }


    /**
     * Get the appropriate initiation service based on gateway type.
     *
     * @param string $gatewayType The gateway type ('satim' or 'poste_dz')
     * @return mixed The initiation service instance
     * @throws PaymentException If gateway type is unsupported
     */
    private function getInitiateService(string $gatewayType)
    {
        return match ($gatewayType) {
            'satim' => app(SatimInitiateService::class),
            'poste_dz' => app(PosteDzInitiateService::class),
            default => throw new PaymentException('Unsupported gateway type', 'UNSUPPORTED_GATEWAY', 400),
        };
    }


    /**
     * Get the appropriate confirmation service based on gateway type.
     *
     * @param string $gatewayType The gateway type ('satim' or 'poste_dz')
     * @return mixed The confirmation service instance
     * @throws PaymentException If gateway type is unsupported
     */
    private function getConfirmService(string $gatewayType)
    {
        return match ($gatewayType) {
            'satim' => app(SatimConfirmService::class),
            'poste_dz' => app(PosteDzConfirmService::class),
            default => throw new PaymentException('Unsupported gateway type', 'UNSUPPORTED_GATEWAY', 400),
        };
    }

    /**
     * Create a new transaction for the payment.
     *
     * @param array $data Payment data
     * @param Application $application The application initiating the payment
     * @return Transaction The created transaction
     */
    private function createTransaction(array $data, Application $application): Transaction
    {
        return Transaction::create([
            'amount' => $data['amount'],
            'order_number' => $this->generateOrderNumber($application),
            'status' => 'initiated',
            'application_id' => $application->id,
            'license_id' => $application->license->id,
            'license_env' => $application->license_env,
            'currency' => '012', // Algerian Dinar
            'partner_id' => $application->partner->id,
            'origin' => $data['origin'] ?? 'Application',
        ]);
    }

    /**
     * Generate a unique order number for the transaction.
     *
     * @param Application $application The application initiating the payment
     * @return string The generated order number
     */
    private function generateOrderNumber(Application $application): string
    {
        $licenseId = $application->license->id;
        $length = $application->license_env === 'development' ? 10 : 20;

        do {
            $unique = uniqid(mt_rand(), true);
            $orderNumber = strtoupper(substr(base_convert($unique, 16, 36), 0, $length));
        } while (Transaction::where('order_number', $orderNumber)->where('license_id', $licenseId)->exists());

        return $orderNumber;
    }
}
