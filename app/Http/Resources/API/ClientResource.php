<?php

namespace App\Http\Resources\API;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
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
                'type' => 'client',
                'id' => $data['id'],
                'attributes' => [
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'partner_id' => $data['partner_id'],
                    'app_key' => $data['app_key'],
                    'app-secret' => $data['app_secret'],
                    'reset_password_flag' => $data['reset_password_flag']
                ]
            ],
            'meta' => [
                'code' => $this->resource['code'],
                'message' => $this->resource['message']
            ]
        ];
    }
}
