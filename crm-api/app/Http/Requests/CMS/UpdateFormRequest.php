<?php

namespace App\Http\Requests\CMS;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'string', 'max:255', Rule::unique('forms')->ignore($this->form)],
            'description' => ['nullable', 'string'],
            'fields' => ['sometimes', 'array'],
            'fields.*.name' => ['required', 'string', 'max:255'],
            'fields.*.type' => ['required', 'string', 'in:text,email,textarea,select,checkbox,radio,file'],
            'fields.*.label' => ['required', 'string', 'max:255'],
            'fields.*.required' => ['boolean'],
            'fields.*.options' => ['nullable', 'array'],
            'settings' => ['nullable', 'array'],
            'submit_button_text' => ['nullable', 'string', 'max:255'],
            'success_message' => ['nullable', 'string'],
            'notification_email' => ['nullable', 'email', 'max:255'],
            'active' => ['sometimes', 'boolean'],
        ];
    }
}
