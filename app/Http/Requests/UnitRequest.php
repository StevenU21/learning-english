<?php

namespace App\Http\Requests;

use App\Models\Unit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UnitRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if ($this->isMethod('post')) {
            return $this->user()->can('create', Unit::class);
        }

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            return $this->user()->can('update', $this->route('unit'));
        }

        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:4', 'max:50', Rule::unique('units')->ignore($this->unit)],
            'description' => ['nullable', 'string', 'max:255'],
            'expected_time' => ['required', 'integer', 'min:1'],
            'image' => [
                'nullable',
                'image',
                'mimes:jpg,png,jpeg,webp',
                'max:4096',
            ],
            'level_id' => ['required', 'exists:levels,id'],
        ];
    }
}
