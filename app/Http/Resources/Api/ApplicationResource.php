<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationResource extends JsonResource
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
                'type' => 'application',
                'id' => $data['id'],
                'attributes' => [
                    'name' => $data['name'],
                    'app_key' => $data['app_key'],
                    'app_secret' => $data['app_secret'],
                    'website_url' => $data['website_url'],
                    'redirect_url' => $data['redirect_url'],
                    'created_at' => $data['created_at'],
                    'updated_at' => $data['updated_at'],
                    'license_id' => $data['license_id'],
                    'license_env' => $data['license_env'],
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
