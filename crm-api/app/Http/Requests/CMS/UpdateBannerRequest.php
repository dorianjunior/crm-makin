<?php

namespace App\Http\Requests\CMS;

use App\Enums\ContentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBannerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'string', 'max:255'],
            'location' => ['sometimes', 'string', 'max:100'],
            'image' => ['sometimes', 'string', 'max:255'],
            'link_url' => ['nullable', 'url', 'max:255'],
            'new_window' => ['sometimes', 'boolean'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
            'status' => ['sometimes', Rule::enum(ContentStatus::class)],
            'order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
