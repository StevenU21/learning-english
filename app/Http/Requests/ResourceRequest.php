<?php

namespace App\Http\Requests;

use App\Models\Resource;
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
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
}
