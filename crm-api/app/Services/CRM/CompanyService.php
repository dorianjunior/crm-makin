<?php

namespace App\Services\CRM;

use App\Models\CRM\Company;
use Illuminate\Support\Collection;

class CompanyService
{
    public function create(array $data): Company
    {
        return Company::create($data);
    }

    public function update(Company $company, array $data): Company
    {
        $company->update($data);

        return $company->fresh();
    }

    public function delete(Company $company): bool
    {
        return $company->delete();
    }

    public function getAll(): Collection
    {
        return Company::with(['users', 'leads'])->get();
    }

    public function activate(Company $company): Company
    {
        $company->update(['status' => 'active']);

        return $company->fresh();
    }

    public function deactivate(Company $company): Company
    {
        $company->update(['status' => 'inactive']);

        return $company->fresh();
    }

    public function suspend(Company $company): Company
    {
        $company->update(['status' => 'suspended']);

        return $company->fresh();
    }

    public function changePlan(Company $company, string $plan): Company
    {
        $company->update(['plan' => $plan]);

        return $company->fresh();
    }

    public function getStatistics(Company $company): array
    {
        return [
            'total_users' => $company->users()->count(),
            'total_leads' => $company->leads()->count(),
            'total_products' => $company->products()->count(),
            'total_pipelines' => $company->pipelines()->count(),
            'active_tasks' => $company->tasks()->whereIn('status', ['pending', 'in_progress'])->count(),
        ];
    }
}
