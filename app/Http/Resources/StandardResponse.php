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
                'attributes' => [
                    'name' => $data['name'] ?? null,
                    'company_name' => $data['company_name'] ?? null,
                    'phone' => $data['phone'] ?? null,
                    'email' => $data['email'] ?? null,
                    'legal_status' => $data['legal_status'] ?? null,
                    'has_bank_account' => $data['has_bank_account'] ?? null,
                    'bank_name' => $data['bank_name'] ?? null,
                    'website_integration' => $data['website_integration'] ?? null,
                    'mobile_integration' => $data['mobile_integration'] ?? null,
                    'website_link' => $data['website_link'] ?? null,
                    'programming_languages' => $data['programming_languages'] ?? null,
                ],
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
