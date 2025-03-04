<?php

namespace App\Services\Payments;

use App\Models\Transaction;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Client\RequestException;

class TransactionUpdater
{
    public function handleInitiationResponse(Transaction $transaction, array $response): void
    {
        $transaction->update([
            'status' => $this->determineInitiationStatus($response),
            'error_code' => $response['errorCode'] ?? null,
            'form_url' => $response['formUrl'],
            'order_id' => $response['orderId'],
        ]);
    }

    public function handleConfirmationResponse(Transaction $transaction, array $response): void
    {
        $transaction->update([
            'status' => $response['Amount'] ? 'paid' : 'failed',
            'confirmation_status' => ($response['ErrorCode'] ?? '1') === '0' ? 'confirmed' : 'failed',
            'deposit_amount' => isset($response['Amount']) ? $response['Amount'] / 100 : null,
            'gateway_response' => json_encode($response)
        ]);
    }

    public function handleRequestError(Transaction $transaction, RequestException $e): void
    {
        $transaction->update([
            'status' => 'gateway_error',
            'error_code' => $e->getCode(),
            'error_message' => $e->response->json()['ErrorMessage'] ?? 'Gateway request failed'
        ]);
    }

    public function markUnreachable(Transaction $transaction): void
    {
        $transaction->update(['status' => 'gateway_unreachable']);
    }

    private function determineInitiationStatus(array $response): string
    {
        return ($response['errorCode'] ?? '1') === '0' ? 'processing' : 'gateway_error';
    }
}