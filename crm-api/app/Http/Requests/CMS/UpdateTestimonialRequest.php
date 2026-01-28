<?php

namespace App\Http\Requests\CMS;

use App\Enums\ContentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTestimonialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'author_name' => ['sometimes', 'string', 'max:255'],
            'author_position' => ['nullable', 'string', 'max:255'],
            'author_company' => ['nullable', 'string', 'max:255'],
            'author_avatar' => ['nullable', 'string', 'max:255'],
            'content' => ['sometimes', 'string'],
            'rating' => ['sometimes', 'integer', 'min:1', 'max:5'],
            'status' => ['sometimes', Rule::enum(ContentStatus::class)],
            'order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
