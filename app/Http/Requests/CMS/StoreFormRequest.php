<?php

namespace App\Http\Requests\CMS;

use Illuminate\Foundation\Http\FormRequest;

class StoreFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'site_id' => ['required', 'exists:sites,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:forms,slug'],
            'description' => ['nullable', 'string'],
            'fields' => ['required', 'array'],
            'fields.*.name' => ['required', 'string', 'max:255'],
            'fields.*.type' => ['required', 'string', 'in:text,email,textarea,select,checkbox,radio,file'],
            'fields.*.label' => ['required', 'string', 'max:255'],
            'fields.*.required' => ['boolean'],
            'fields.*.options' => ['nullable', 'array'],
            'settings' => ['nullable', 'array'],
            'submit_button_text' => ['nullable', 'string', 'max:255'],
            'success_message' => ['nullable', 'string'],
            'notification_email' => ['nullable', 'email', 'max:255'],
            'active' => ['boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'created_by' => auth()->id(),
            'active' => $this->active ?? true,
        ]);
    }
}
