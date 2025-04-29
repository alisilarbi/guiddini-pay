<?php

namespace App\Http\Requests\Api\License;

use Illuminate\Foundation\Http\FormRequest;

class StoreLicenseRequest extends FormRequest
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
            'name' => 'required|string',
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

            $prodMissing = [];
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

            if (!$this->filled('satim_development_username') && !$this->filled('satim_production_username')) {
                $validator->errors()->add('environment', 'At least one environment (development or production) must be provided.');
            }
        });
    }


    // public function authorize(): bool
    // {
    //     return true;
    // }

    // /**
    //  * Get the validation rules that apply to the request.
    //  *
    //  * @return array<string, string>
    //  */
    // public function rules(): array
    // {
    //     return [
    //         'name' => 'required|string|max:255',
    //         'satim_development_username' => 'nullable|string|max:255',
    //         'satim_development_password' => 'nullable|string|max:255',
    //         'satim_development_terminal' => 'nullable|string|max:255',
    //         'satim_production_username' => 'nullable|string|max:255',
    //         'satim_production_password' => 'nullable|string|max:255',
    //         'satim_production_terminal' => 'nullable|string|max:255',
    //     ];
    // }

    // /**
    //  * Add custom validation for environment credentials.
    //  *
    //  * @param Validator $validator
    //  */
    // public function withValidator(Validator $validator): void
    // {
    //     $validator->after(function (Validator $validator) {
    //         $this->validateEnvironmentCredentials($validator, 'development');
    //         $this->validateEnvironmentCredentials($validator, 'production');
    //         $this->validateAtLeastOneEnvironment($validator);
    //     });
    // }

    // /**
    //  * Validate that all credentials for an environment are provided together.
    //  *
    //  * @param Validator $validator
    //  * @param string $environment
    //  */
    // private function validateEnvironmentCredentials(Validator $validator, string $environment): void
    // {
    //     $fields = [
    //         "satim_{$environment}_username",
    //         "satim_{$environment}_password",
    //         "satim_{$environment}_terminal",
    //     ];

    //     $missing = array_filter($fields, fn($field) => !$this->filled($field));

    //     if (count($missing) > 0 && count($missing) < count($fields)) {
    //         $validator->errors()->add(
    //             "satim_{$environment}_credentials",
    //             "All {$environment} credentials are required together. Missing: " . implode(', ', $missing) . '.'
    //         );
    //     }
    // }

    // /**
    //  * Validate that at least one environment has credentials.
    //  *
    //  * @param Validator $validator
    //  */
    // private function validateAtLeastOneEnvironment(Validator $validator): void
    // {
    //     $hasDevelopment = $this->filled('satim_development_username');
    //     $hasProduction = $this->filled('satim_production_username');

    //     if (!$hasDevelopment && !$hasProduction) {
    //         $validator->errors()->add(
    //             'environment',
    //             'At least one environment (development or production) must have credentials provided.'
    //         );
    //     }
    // }
}
