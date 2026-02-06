<?php

namespace App\Http\Requests\CRM;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'lead_id' => ['required', 'exists:leads,id'],
            'type' => ['required', 'string', Rule::in(['call', 'meeting', 'email', 'note', 'task'])],
            'description' => ['required', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'duration' => ['nullable', 'integer', 'min:1', 'max:1440'],
        ];
    }

    public function messages(): array
    {
        return [
            'lead_id.required' => 'O lead é obrigatório.',
            'lead_id.exists' => 'Lead não encontrado.',
            'type.required' => 'O tipo de atividade é obrigatório.',
            'type.in' => 'Tipo de atividade inválido.',
            'description.required' => 'A descrição é obrigatória.',
            'description.max' => 'A descrição não pode ter mais de 500 caracteres.',
            'notes.max' => 'As notas não podem ter mais de 2000 caracteres.',
            'duration.min' => 'A duração mínima é de 1 minuto.',
            'duration.max' => 'A duração máxima é de 1440 minutos (24 horas).',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'user_id' => $this->user()->id,
            'company_id' => $this->user()->company_id,
        ]);
    }
}
