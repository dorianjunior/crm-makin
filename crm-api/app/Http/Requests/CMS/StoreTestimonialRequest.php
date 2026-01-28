<?php

namespace App\Http\Requests\CMS;

use App\Enums\ContentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTestimonialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'site_id' => ['required', 'exists:sites,id'],
            'author_name' => ['required', 'string', 'max:255'],
            'author_position' => ['nullable', 'string', 'max:255'],
            'author_company' => ['nullable', 'string', 'max:255'],
            'author_avatar' => ['nullable', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'status' => ['required', Rule::enum(ContentStatus::class)],
            'order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'created_by' => auth()->id(),
        ]);
    }
}
