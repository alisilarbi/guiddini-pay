<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LicenseResource extends JsonResource
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
                'type' => 'License',
                'id' => $data['id'],
                'attributes' => [
                    'name' => $data['name'],
                    'satim_development_username' => $data['satim_development_username'],
                    'satim_development_password' => $data['satim_development_password'],
                    'satim_development_terminal' => $data['satim_development_terminal'],

                    'satim_production_username' => $data['satim_production_username'],
                    'satim_production_password' => $data['satim_production_password'],
                    'satim_production_terminal' => $data['satim_production_terminal'],
                ],
                'meta' => [
                    'code' => $this->resource['code'],
                    'message' => $this->resource['message']
                ]
            ]
        ];
    }

    public function withResponse($request, $response)
    {
        $response->setStatusCode($this->resource['http_code'] ?? 200);
    }
}
