<?php

namespace App\Http\Requests\Api\License;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLicenseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id' => 'required|string',
            'name' => 'nullable|string',
            'satim_development_username' => 'nullable|string',
            'satim_development_password' => 'nullable|string',
            'satim_development_terminal' => 'nullable|string',
            'satim_production_username' => 'nullable|string',
            'satim_production_password' => 'nullable|string',
            'satim_production_terminal' => 'nullable|string',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $devMissing = [];
            if ($this->hasAny(['satim_development_username', 'satim_development_password', 'satim_development_terminal'])) {
                if (!$this->filled('satim_development_username')) {
                    $devMissing[] = 'satim_development_username';
                }
                if (!$this->filled('satim_development_password')) {
                    $devMissing[] = 'satim_development_password';
                }
                if (!$this->filled('satim_development_terminal')) {
                    $devMissing[] = 'satim_development_terminal';
                }
                if (count($devMissing) > 0 && count($devMissing) < 3) {
                    $validator->errors()->add('satim_development_credentials', 'All development credentials are required together. You are missing ' . implode(', ', $devMissing) . '.');
                }
            }

            $prodMissing = [];
            if ($this->hasAny(['satim_production_username', 'satim_production_password', 'satim_production_terminal'])) {
                if (!$this->filled('satim_production_username')) {
                    $prodMissing[] = 'satim_production_username';
                }
                if (!$this->filled('satim_production_password')) {
                    $prodMissing[] = 'satim_production_password';
                }
                if (!$this->filled('satim_production_terminal')) {
                    $prodMissing[] = 'satim_production_terminal';
                }
                if (count($prodMissing) > 0 && count($prodMissing) < 3) {
                    $validator->errors()->add('satim_production_credentials', 'All production credentials are required together. You are missing ' . implode(', ', $prodMissing) . '.');
                }
            }

            if (!$this->filled('satim_development_username') && !$this->filled('satim_production_username')) {
                $validator->errors()->add('environment', 'At least one environment (development or production) must be provided.');
            }
        });
    }
}
