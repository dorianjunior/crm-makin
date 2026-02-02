<?php

namespace App\Http\Requests\CMS;

use App\Enums\ContentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'site_id' => ['required', 'exists:sites,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['sometimes', 'string', 'max:255', 'unique:pages,slug'],
            'excerpt' => ['sometimes', 'nullable', 'string'],
            'content' => ['required', 'string'],
            'seo_title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'seo_description' => ['sometimes', 'nullable', 'string'],
            'seo_keywords' => ['sometimes', 'nullable', 'string', 'max:255'],
            'featured_image' => ['sometimes', 'nullable', 'string', 'max:255'],
            'status' => ['sometimes', Rule::enum(ContentStatus::class)],
            'published_at' => ['sometimes', 'nullable', 'date'],
            'meta_data' => ['sometimes', 'array'],
            'order' => ['sometimes', 'integer', 'min:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'created_by' => $this->user()->id,
        ]);
    }
}
