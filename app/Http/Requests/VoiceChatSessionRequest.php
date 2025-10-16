<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VoiceChatSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'voice' => ['nullable', 'string', 'max:50'],
            'instructions' => ['nullable', 'string', 'max:1000'],
            'level' => ['nullable', 'in:basico,intermedio,avanzado'],
        ];
    }
}
