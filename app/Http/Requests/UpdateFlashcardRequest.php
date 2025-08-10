<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFlashcardRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('deck'));
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'question' => 'required|string|max:' . config('flashcard.flashcards.question_max_length'),
            'answer' => 'required|string|max:' . config('flashcard.flashcards.answer_max_length'),
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'question' => 'flashcard question',
            'answer' => 'flashcard answer',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'question.required' => __('validation.flashcard.question_required'),
            'answer.required' => __('validation.flashcard.answer_required'),
            'question.max' => __('validation.flashcard.question_max'),
            'answer.max' => __('validation.flashcard.answer_max'),
        ];
    }
} 