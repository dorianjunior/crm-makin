<?php

namespace App\Http\Requests\CMS;

use App\Enums\ContentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFaqRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category' => ['nullable', 'string', 'max:255'],
            'question' => ['sometimes', 'string'],
            'answer' => ['sometimes', 'string'],
            'status' => ['sometimes', Rule::enum(ContentStatus::class)],
            'order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
