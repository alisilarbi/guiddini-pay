<?php

namespace App\Services\Payments\Gateways\PosteDz;

use App\Models\Transaction;
use Illuminate\Http\Client\RequestException;

class PosteDzTransactionUpdater
{
    /**
     * Update transaction with initiation response data.
     *
     * @param Transaction $transaction The transaction to update
     * @param array $response The gateway's initiation response
     * @return void
     */
    public function handleInitiationResponse(Transaction $transaction, array $response): void
    {
        $transaction->update([
            'status' => $this->determineInitiationStatus($response),
            'error_code' => $response['errorCode'] ?? null,
            'form_url' => $response['formUrl'] ?? null,
            'order_id' => $response['orderId'] ?? null,
        ]);
    }

    /**
     * Update transaction with confirmation response data from getOrderStatus.do.
     *
     * @param Transaction $transaction The transaction to update
     * @param array $response The gateway's confirmation response
     * @return void
     */
    public function handleConfirmationResponse(Transaction $transaction, array $response): void
    {
        $updateData = [
            'error_code'        => (int)($response['errorCode'] ?? 7),
            'order_status_description' => $response['orderStatusDescription'] ?? null,
            'order_status'      => (int)($response['orderStatus'] ?? -1),
            // 'error_message'     => $response['errorMessage'] ?? null,
            'pan'               => $response['pan'] ?? null,
            'expiration'        => $response['expiration'] ?? null,
            'cardholder_name'   => $response['cardholderName'] ?? null,
            'amount'            => isset($response['amount']) ? $response['amount'] / 100 : null,
            'currency'          => $response['currency'] ?? null,
        ];

        $errorCode = $updateData['error_code'];
        $orderStatus = $updateData['order_status'];

        if ($errorCode === 0) {
            // Success path — check orderStatus
            $updateData['status'] = match ($orderStatus) {
                2 => 'paid',
                0 => 'pending',
                6 => 'declined',
                5 => 'access_denied',
            } ?? 'unknown';

            $updateData['confirmation_status'] = match ($orderStatus) {
                2 => 'confirmed',
                0 => 'pending',
                6, 5 => 'failed',
            } ?? 'failed';
        } else {
            // Error path — map known error codes
            $updateData['status'] = match ($errorCode) {
                5 => 'invalid_request',   // Bad params or user permission issue
                6 => 'invalid_order',     // Order not found
                7 => 'system_error',      // Payment state error or system error
                default => 'error',
            };

            $updateData['confirmation_status'] = 'failed';
        }


        $updateData['transaction_status'] = $updateData['status'];
        $updateData['transaction_status_message'] = $updateData['order_status_description'] ?? 'No error message provided';

        // $updateData['action_code'] = $updateData['order_status'];
        $updateData['action_code_description'] = $updateData['order_status_description'];

        $transaction->update($updateData);
    }

    /**
     * Update transaction with error details from a failed request.
     *
     * @param Transaction $transaction The transaction to update
     * @param RequestException $e The exception thrown during the request
     * @return void
     */
    public function handleRequestError(Transaction $transaction, RequestException $e): void
    {
        $transaction->update([
            'status' => 'gateway_error',
            'error_code' => $e->getCode(),
            'error_message' => $e->response->json()['ErrorMessage'] ?? 'Gateway request failed'
        ]);
    }

    /**
     * Mark transaction as unreachable if the gateway cannot be contacted.
     *
     * @param Transaction $transaction The transaction to update
     * @return void
     */
    public function markUnreachable(Transaction $transaction): void
    {
        $transaction->update(['status' => 'gateway_unreachable']);
    }

    /**
     * Determine the initiation status based on the response.
     *
     * @param array $response The gateway's initiation response
     * @return string The determined status
     */
    private function determineInitiationStatus(array $response): string
    {
        if (($response['errorCode'] ?? '1') === '5') {
            return 'gateway_denied';
        }

        return ($response['errorCode'] ?? '1') === '0' ? 'processing' : 'gateway_error';
    }
}
