<?php

namespace App\Http\Requests\CRM;

use App\Enums\CompanyPlan;
use App\Enums\CompanyStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'domain' => ['nullable', 'string', 'max:255'],
            'plan' => ['required', Rule::enum(CompanyPlan::class)],
            'status' => ['required', Rule::enum(CompanyStatus::class)],
        ];
    }
}
