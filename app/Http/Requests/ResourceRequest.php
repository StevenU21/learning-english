<?php

namespace App\Http\Requests;

use App\Models\Resource;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ResourceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if ($this->isMethod('post')) {
            return $this->user()->can('create', Resource::class);
        }

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            return $this->user()->can('update', $this->route('resource'));
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
        $rules = [
            'name' => ['required', 'string', 'min:6', 'max:50'],
            'description' => ['nullable', 'string'],
            'unit_id' => ['required', 'exists:units,id'],
        ];

        if ($this->isMethod('post')) {
            $rules['file_path'] = ['required', 'file', 'mimes:jpg,png,jpeg,webp,pdf,doc,docx,xls,xlsx,ppt,pptx', 'max:30240'];
        }

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $rules['file_path'] = ['nullable', 'file', 'mimes:jpg,png,jpeg,webp,pdf,doc,docx,xls,xlsx,ppt,pptx', 'max:30240'];
        }

        return $rules;
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
            'unit_id' => 'unidad',
            'file_path' => 'archivo',
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
            'description.string' => 'La descripción debe ser una cadena de texto.',
            'unit_id.required' => 'La unidad es obligatoria.',
            'unit_id.exists' => 'La unidad seleccionada no es válida.',
            'file_path.required' => 'El archivo es obligatorio.',
            'file_path.file' => 'El archivo debe ser un archivo válido.',
            'file_path.mimes' => 'El archivo debe ser de tipo: :values.',
            'file_path.max' => 'El archivo no debe exceder de :max kilobytes.',
        ];
    }
}
