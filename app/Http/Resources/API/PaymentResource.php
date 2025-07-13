<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = $this->resource['data'];

        dd($data);

        return [
            'data' => [
                'type' => 'transaction',
                'id' => $data['transaction']['order_number'],
                'attributes' => [
                    'amount' => $data['transaction']['amount'],
                    'status' => $data['transaction']['status'],
                    'form_url' => $data['formUrl'],
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
