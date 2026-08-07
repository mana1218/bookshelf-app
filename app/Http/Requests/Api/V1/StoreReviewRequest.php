<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookRequest extends FormRequest
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
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['required', 'max:255']
        ];
    }

    public function messages(): array
    {
        return [
            'rating.required' => '評価を入力してください。',
            'rating.integer' => '評価は整数で入力してください。',
            'rating.between' => '評価は1〜5の範囲で入力してください。',
            'comment.required' => 'コメントを入力してください。',
            'comment.max' => 'コメントは255文字以内で入力してください。'
        ];
    }
}
