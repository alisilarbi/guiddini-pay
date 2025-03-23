<?php

namespace App\Http\Resources\API;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProspectResource extends JsonResource
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
                'type' => 'prospect',
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
                    'reference' => $data['reference'],
                    'needs_help' => $data['needs_help'],
                    'partner_id' => $data['partner_id'],
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
