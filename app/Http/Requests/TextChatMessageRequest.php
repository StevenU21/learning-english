<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TextChatMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'messages' => ['required', 'array', 'min:1'],
            'messages.*.role' => ['required', 'in:user,assistant'],
            'messages.*.content' => ['required', 'string', 'max:2000'],
            'level' => ['nullable', 'in:basico,intermedio,avanzado'],
            'temperature' => ['nullable', 'numeric', 'between:0,2'],
        ];
    }
}
