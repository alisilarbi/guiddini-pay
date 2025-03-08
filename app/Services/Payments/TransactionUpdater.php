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

        $updateData = [
            'deposit_amount' => isset($response['depositAmount']) ? $response['depositAmount'] / 100 : null,
            'auth_code' => $response['authCode'] ?? null,
            'action_code' => $response['actionCode'] ?? null,
            'action_code_description' => $response['actionCodeDescription'] ?? null,
            'ErrorCode' => $response['ErrorCode'] ?? null,
            'ErrorMessage' => $response['ErrorMessage'] ?? null,
            'svfe_response' => $response['svfe_response'] ?? null,
            'pan' => $response['Pan'] ?? null,
            'ip_address' => $response['Ip'] ?? null,
            'status' => $response['status'] ?? null,
            'confirmation_status' => $response['confirmation_status'] ?? null,
        ];

        $isSuccess = false;


        dd([
            'response' => $response,
            'updateData' => $updateData,
        ]);


        if (($response['ErrorCode'] ?? 1) === 0 && ($response['actionCode'] ?? 1) === 0)
            $isSuccess = true;

        $errorType = match ((int)($response['actionCode'] ?? -1)) {
            0 => null, // Success
            10 => 'user_cancelled',
            116 => 'insufficient_funds',
            -1, 111 => 'bank_rejection',
            default => 'general_failure'
        };

        $updateData['status'] = $isSuccess ? 'paid' : ($errorType ?? 'failed');
        $updateData['confirmation_status'] = $isSuccess ? 'confirmed' : 'failed';
        dd($updateData);

        $transaction->update([
            'deposit_amount' => $updateData['deposit_amount'],
            'auth_code' => $updateData['auth_code'],
            'action_code' => $updateData['action_code'],
            'action_code_description' => $updateData['action_code_description'],
            'status' => $updateData['status'],
            'svfe_response' => $updateData['svfe_response'],
            'pan' => $updateData['pan'],
            'ip_address' => $updateData['ip_address'],
            'confirmation_status' => $updateData['confirmation_status'],
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
