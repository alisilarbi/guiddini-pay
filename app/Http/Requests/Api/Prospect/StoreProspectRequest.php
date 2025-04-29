<?php

namespace App\Http\Requests\Api\Prospect;

use Illuminate\Foundation\Http\FormRequest;

class StoreProspectRequest extends FormRequest
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
            'company_name' => 'nullable|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'legal_status' => 'nullable|string',
            'has_bank_account' => 'boolean',
            'bank_name' => 'nullable|string',
            'website_integration' => 'boolean',
            'mobile_integration' => 'boolean',
            'website_url' => 'nullable|string',
            'programming_languages' => 'nullable|json',
            'needs_help' => 'nullable|boolean',
        ];
    }
}
