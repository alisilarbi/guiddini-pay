<?php

namespace App\Http\Resources\API;

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

        $meta = [
            'code' => $this->resource['code'],
            'message' => $this->resource['message']
        ];

        if (isset($this->resource['http_code'])) {
            $meta['http_code'] = $this->resource['http_code'];
        }


        return [
            'data' => [
                'type' => 'transaction',
                'id' => $data['transaction']['order_number'],
                'attributes' => [
                    'amount' => $data['transaction']['amount'],
                    'status' => $data['transaction']['status'],
                    'form_url' => $data['formUrl']
                ]
            ],
            'meta' => $meta
        ];
    }

    public function withResponse($request, $response)
    {
        $response->setStatusCode($this->resource['http_code'] ?? 200);
    }
}
