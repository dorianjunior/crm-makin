<?php

namespace App\Http\Requests\CRM;

use App\Enums\CompanyPlan;
use App\Enums\CompanyStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'domain' => ['nullable', 'string', 'max:255'],
            'plan' => ['sometimes', Rule::enum(CompanyPlan::class)],
            'status' => ['sometimes', Rule::enum(CompanyStatus::class)],
        ];
    }
}
