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
    public function toArray(Request $request): array
    {
        return [
            'success' => $this->resource['success'] ?? false,
            'code' => $this->resource['code'] ?? ($this->resource['success'] ? 'SUCCESS' : 'ERROR'),
            'message' => $this->resource['message'] ?? ($this->resource['success'] ? 'Operation successful' : 'Operation failed'),
            'data' => $this->resource['data'] ?? [],
            'errors' => $this->resource['errors'] ?? null
        ];
    }
}
