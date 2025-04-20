<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ErrorResource extends JsonResource
{

    public static $wrap = null;


    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'errors' => [
                [
                    'status' => (string)($this->resource['http_code'] ?? 500),
                    'code' => $this->resource['code'] ?? 'INTERNAL_ERROR',
                    'title' => $this->resource['message'] ?? 'Unexpected error occurred',
                    'detail' => $this->resource['detail'] ?? null,
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
