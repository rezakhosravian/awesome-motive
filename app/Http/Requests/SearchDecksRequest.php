<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchDecksRequest extends FormRequest
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
            'q' => 'required|string|min:1|max:255',
            'per_page' => 'sometimes|integer|min:1|max:50',
            'page' => 'sometimes|integer|min:1'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'q' => 'search query',
            'per_page' => 'items per page',
            'page' => 'page number',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'q.required' => __('validation.search.query_required'),
            'q.string' => __('validation.search.query_string'),
            'q.min' => __('validation.search.query_min'),
            'q.max' => __('validation.search.query_max'),
            'per_page.integer' => __('validation.search.per_page_integer'),
            'per_page.min' => __('validation.search.per_page_min'),
            'per_page.max' => __('validation.search.per_page_max'),
            'page.integer' => __('validation.search.page_integer'),
            'page.min' => __('validation.search.page_min'),
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if (!$this->has('per_page')) {
            $this->merge([
                'per_page' => 15
            ]);
        }

        if ($this->has('page') && $this->input('page') < 1) {
            $this->merge([
                'page' => 1
            ]);
        }
    }

    /**
     * Get the search query.
     */
    public function getQuery(): string
    {
        return $this->validated('q');
    }

    /**
     * Get the per page value.
     */
    public function getPerPage(): int
    {
        return $this->validated('per_page', 15);
    }

    /**
     * Get the page number.
     */
    public function getPage(): int
    {
        return $this->validated('page', 1);
    }
}
