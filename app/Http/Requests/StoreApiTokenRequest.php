<?php

namespace App\Http\Requests;

use App\Models\ApiToken;
use Illuminate\Foundation\Http\FormRequest;

class StoreApiTokenRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Determine if the request is expecting a JSON response.
     */
    public function expectsJson(): bool
    {
        return true;
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new \Illuminate\Validation\ValidationException($validator);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'abilities' => 'sometimes|array',
            'abilities.*' => 'string|in:read,write,delete,admin',
            'expires_at' => 'sometimes|date|after:today'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'token name',
            'abilities' => 'token abilities',
            'abilities.*' => 'ability',
            'expires_at' => 'expiration date',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => __('validation.api_token.name_required'),
            'name.string' => __('validation.api_token.name_string'),
            'name.max' => __('validation.api_token.name_max'),
            'abilities.array' => __('validation.api_token.abilities_array'),
            'abilities.*.string' => __('validation.api_token.ability_string'),
            'abilities.*.in' => __('validation.api_token.ability_invalid'),
            'expires_at.date' => __('validation.api_token.expires_at_date'),
            'expires_at.after' => __('validation.api_token.expires_at_future'),
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if (!$this->has('abilities')) {
            $this->merge([
                'abilities' => ['read', 'write']
            ]);
        }
    }
}
