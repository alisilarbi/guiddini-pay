<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResponseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public static $wrap = null;

    public function toArray(Request $request): array
    {
        if ($this->resource['success'] ?? false) {
            return $this->formatSuccessResponse();
        }

        return $this->formatErrorResponse();
    }

    private function formatSuccessResponse(): array
    {
        $data = $this->resource['data'];

        return [
            'data' => [
                'type' => 'transaction',
                'id' => $data['transaction']['order_number'],
                'attributes' => [
                    'amount' => $data['transaction']['amount'],
                    'status' => $data['transaction']['status'],
                    // 'confirmation_status' => $data['transaction']['confirmation_status'],
                    'form_url' => $data['formUrl']
                ],
                // 'links' => [
                //     'self' => route('payment.status', $data['transaction']['order_number']),
                //     'confirm' => route('payment.confirm', $data['transaction']['order_number'])
                // ]
            ],
            // 'meta' => [
            //     'code' => $this->resource['code'],
            //     'message' => $this->resource['message']
            // ]
        ];
    }

    private function formatErrorResponse(): array
    {
        return [
            'errors' => [
                [
                    'status' => (string)($this->resource['http_code'] ?? 500),
                    'code' => $this->resource['code'] ?? 'INTERNAL_ERROR',
                    'title' => $this->resource['message'] ?? 'Unexpected error occurred',
                    'detail' => $this->resource['errors']['system'] ?? null,
                    'meta' => $this->resource['errors'] ?? []
                ]
            ]
        ];
    }

    public function withResponse($request, $response)
    {
        $response->setStatusCode($this->resource['http_code'] ?? 500);
    }

}
