<?php

namespace App\Services\InternalPayments;

use App\Models\Transaction;
use App\Exceptions\PaymentException;
use App\Services\Payments\TransactionUpdater;
use App\Services\InternalPayments\InternalTransactionUpdater;
use App\Services\InternalPayments\InternalConfirmGatewayService;
use App\Services\InternalPayments\InternalInitiateGatewayService;

class InternalPaymentService
{
    public function __construct(
        private InternalInitiateGatewayService $initiator,
        private InternalConfirmGatewayService $confirmer,
        private InternalTransactionUpdater $updater
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
            'origin' => 'Quota',
            'amount' => $data['amount'],
            'order_number' => $this->generateOrderNumber(),
            'status' => 'initiated',
            'license_env' => $env,
            'currency' => '012',
            'partner_id' => $data['partner_id'],
            'origin' => $data['origin'] ?? 'Quota Debt',
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