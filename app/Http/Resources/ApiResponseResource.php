<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApiResponseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public static $wrap = null;

    public function toArray($request): array
    {
        if ($this->resource['success'] ?? false) {
            return [
                'data' => $this->resource['data'] ?? null,
                'meta' => [
                    'code' => $this->resource['code'] ?? 'SUCCESS',
                    'message' => $this->resource['message'] ?? 'Operation succeeded',
                ]
            ];
        }

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
