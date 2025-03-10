<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StandardResponse extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        if ($this->resource['success'] ?? false)
            return $this->formatSuccessResponse();

        return $this->formatErrorResponse();
    }

    private function formatSuccessResponse()
    {
        $data = $this->resource['data'];

        return [
            'data' => [
                'type' => $data['type'],
                'id' => $data['id'],
                'attributes' => [],
                'meta' => [
                    'message' => $this->resource['message'],
                ]
            ]
        ];
    }

    public function formatErrorResponse()
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
        $response->setStatusCode($this->resoruce['http_code'] ?? 500);
    }

}
