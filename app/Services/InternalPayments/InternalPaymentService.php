<?php

namespace App\Services\InternalPayments;

use App\Models\Transaction;
use App\Exceptions\PaymentException;
use App\Services\Payments\InternalConfirmGatewayService;
use App\Services\Payments\InternalInitiateGatewayService;

class InternalPaymentService
{
    public function __construct(
        private InternalInitiateGatewayService $initiator,
        private InternalConfirmGatewayService $confirmer,
        private TransactionUpdater $updater
    ) {}

    public function initiatePayment(array $data): array
    {
        $env = config('payments.env');
        $transaction = $this->createTransaction($data, $env);

        $response = $this->initiator->execute($transaction);

        return [
            'formUrl' => $response['formUrl'],
            'transaction' => $transaction->only(['order_number', 'status', 'amount'])
        ];
    }

    public function confirmPayment(string $orderNumber): array
    {
        $transaction = Transaction::where('order_number', $orderNumber)
            ->whereNull('application_id')
            ->firstOrFail();

        $response = $this->confirmer->execute($transaction);

        return [
            'transaction' => $transaction,
            'gateway_response' => $response
        ];
    }

    private function createTransaction(array $data, string $env): Transaction
    {
        return Transaction::create([
            'amount' => $data['amount'],
            'order_number' => $this->generateOrderNumber(),
            'status' => 'initiated',
            'application_id' => null,
            'license_id' => null,
            'license_env' => $env,
            'currency' => '012',
            'partner_id' => null,
            'origin' => $data['origin'] ?? 'Internal',
        ]);
    }

    private function generateOrderNumber(): string
    {
        do {
            $unique = uniqid(mt_rand(), true);
            $orderNumber = strtoupper(substr(base_convert($unique, 16, 36), 0, 20));
        } while (Transaction::where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }
}