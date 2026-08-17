<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IndexBookRequest extends FormRequest
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
            'keyword' => ['nullable', 'string', 'max:255'],
            'genre' => ['nullable', 'integer', 'exists:genres,id'],
            'sort' => ['nullable', 'in:newest,oldest,title,rating']
        ];
    }

    public function messages(): array
    {
        return [
            'keyword.string' => 'キーワードは文字列で入力してください。',
            'keyword.max' => 'キーワードは255文字以内で入力してください。',
            'genre.integer' => 'ジャンルの指定が不正です。',
            'genre.exists' => '指定されたジャンルは存在しません。',
            'sort.in' => '並び順の指定が不正です。',
        ];
    }
}
