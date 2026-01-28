<?php

namespace App\Http\Requests\CMS;

use App\Enums\ContentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePortfolioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'slug' => ['sometimes', 'string', 'max:255', Rule::unique('portfolios')->ignore($this->portfolio)],
            'description' => ['nullable', 'string'],
            'client_name' => ['nullable', 'string', 'max:255'],
            'project_url' => ['nullable', 'url', 'max:255'],
            'images' => ['nullable', 'array'],
            'images.*' => ['string', 'max:255'],
            'technologies' => ['nullable', 'array'],
            'technologies.*' => ['string', 'max:100'],
            'completion_date' => ['nullable', 'date'],
            'status' => ['sometimes', Rule::enum(ContentStatus::class)],
            'order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
