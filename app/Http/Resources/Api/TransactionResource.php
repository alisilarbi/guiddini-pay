<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = $this->resource['data'];
        return [
            'data' => [
                'type' => 'transaction',
                'id' => $data['transaction']['order_number'],
                'attributes' => [
                    'amount' => $data['transaction']['amount'],
                    'order_number' => $data['transaction']['order_number'],
                    'order_id' => $data['transaction']['order_id'],
                    'status' => $data['transaction']['status'],
                    'deposit_amount' => $data['transaction']['deposit_amount'],
                    'auth_code' => $data['transaction']['auth_code'],
                    'action_code' => $data['transaction']['action_code'],
                    'action_code_description' => $data['transaction']['action_code_description'],
                    'error_code' => $data['transaction']['error_code'],
                    'error_message' => $data['transaction']['error_message'],
                    'confirmation_status' => $data['transaction']['confirmation_status'],
                    'license_env' => $data['transaction']['license_env'],
                    'form_url' => $data['transaction']['form_url'],
                    'svfe_response' => $data['transaction']['svfe_response'],
                    'pan' => $data['transaction']['pan'],
                    'ip_address' => $data['transaction']['ip_address'],
                    'approval_code' => $data['transaction']['approval_code'],
                    // 'updated_at' => \Carbon\Carbon::parse($data['transaction']['updated_at'])->toRfc3339String(),
                    'updated_at' => \Carbon\Carbon::parse($data['transaction']['updated_at'], config('app.timezone'))->toRfc3339String(),
                ],
                'links' => [
                    'self' => route('api.client.payment.show', ['order_number' => $data['transaction']['order_number']]) ?? null,
                    'href' => $data['receipt_url'] ?? null,
                ]
            ],
            'meta' => [
                'code' => $this->resource['code'],
                'message' => $this->resource['message']
            ]
        ];
    }

    public function withResponse($request, $response)
    {
        $response->setStatusCode($this->resource['http_code'] ?? 200);
    }
}
