<?php

namespace App\Http\Requests\Api\Prospect;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProspectRequest extends FormRequest
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
            'name' => 'sometimes|required|string',
            'company_name' => 'sometimes|required|string',
            'phone' => 'sometimes|required|string',
            'email' => 'sometimes|required|string',
            'legal_status' => 'sometimes|required|string',
            'has_bank_account' => 'sometimes|required|boolean',
            'bank_name' => 'sometimes|required|string',
            'converted' => 'sometimes|required|boolean',
            'website_integration' => 'sometimes|required|boolean',
            'mobile_integration' => 'sometimes|required|boolean',
            'needs_help' => 'sometimes|required|string',
            'reference' => 'sometimes|required|string',
            'website_url' => 'sometimes|required|string',
            'programming_languages' => 'sometimes|required|json',
        ];
    }
}
