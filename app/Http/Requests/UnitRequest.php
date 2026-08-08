<?php

namespace App\Http\Requests;

use App\Models\Unit;
use Illuminate\Contracts\Validation\ValidationRule;
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:4', 'max:50', Rule::unique('units')->ignore($this->unit)],
            'description' => ['nullable', 'string', 'max:255'],
            'image' => [
                'nullable',
                'image',
                'mimes:jpg,png,jpeg,webp',
                'max:4096',
            ],
            'level_id' => ['required', 'exists:levels,id'],
        ];
    }

    /**
     * Custom attribute names in Spanish.
     *
     * @return array<string,string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'description' => 'descripción',
            'image' => 'imagen',
            'level_id' => 'nivel',
        ];
    }

    /**
     * Custom validation messages in Spanish.
     *
     * @return array<string,string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio.',
            'name.string' => 'El nombre debe ser una cadena de texto.',
            'name.min' => 'El nombre debe tener al menos :min caracteres.',
            'name.max' => 'El nombre no debe exceder de :max caracteres.',
            'name.unique' => 'El nombre ya está en uso.',
            'description.string' => 'La descripción debe ser una cadena de texto.',
            'description.max' => 'La descripción no debe exceder de :max caracteres.',
            'image.image' => 'La imagen debe ser un archivo de imagen válido.',
            'image.mimes' => 'La imagen debe ser de tipo: :values.',
            'image.max' => 'La imagen no debe exceder de :max kilobytes.',
            'level_id.required' => 'El nivel es obligatorio.',
            'level_id.exists' => 'El nivel seleccionado no es válido.',
        ];
    }
}
