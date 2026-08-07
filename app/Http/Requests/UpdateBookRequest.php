<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBookRequest extends FormRequest
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
            'title' => ['required', 'max:255'],
            'author' => ['required', 'max:255'],
            'isbn' => ['required', 'digits:13', Rule::unique('books','isbn')->ignore($this->book)],
            'published_date' => ['required', 'date'],
            'genres' => ['required', 'array', 'min:1'],
            'image_url' => ['nullable', 'url']
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'タイトル',
            'author' => '著者名',
            'isbn' => 'ISBN',
            'published_date' => '出版日',
            'genres' => 'ジャンル',
            'image_url' => '画像URL'
        ];
    }
}
