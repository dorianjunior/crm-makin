<?php

namespace App\Services\CRM;

use App\Models\CRM\Lead;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class LeadService
{
    public function create(array $data): Lead
    {
        return Lead::create($data);
    }

    public function update(Lead $lead, array $data): Lead
    {
        $lead->update($data);

        return $lead->fresh();
    }

    public function delete(Lead $lead): bool
    {
        return $lead->delete();
    }

    public function getByCompany(array $filters = []): LengthAwarePaginator
    {
        $companyId = auth()->user()->company_id;
        $query = Lead::where('company_id', $companyId);

        // Filtros
        if (isset($filters['status']) && $filters['status']) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['assigned_to']) && $filters['assigned_to']) {
            $query->where('assigned_to', $filters['assigned_to']);
        }

        if (isset($filters['source_id']) && $filters['source_id']) {
            $query->where('source_id', $filters['source_id']);
        }

        if (isset($filters['search']) && $filters['search']) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('company', 'like', "%{$search}%");
            });
        }

        // Ordenação
        $sortField = $filters['sort'] ?? 'created_at';
        $sortDirection = $filters['direction'] ?? 'desc';
        $query->orderBy($sortField, $sortDirection);

        return $query->with(['source', 'assignedUser'])->paginate(15);
    }

    public function getStats(int $companyId): array
    {
        $total = Lead::where('company_id', $companyId)->count();

        $newThisMonth = Lead::where('company_id', $companyId)
            ->where('status', 'new')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $qualified = Lead::where('company_id', $companyId)
            ->where('status', 'qualified')
            ->count();

        $won = Lead::where('company_id', $companyId)
            ->where('status', 'won')
            ->count();

        $conversionRate = $total > 0 ? round(($won / $total) * 100, 1) : 0;

        return [
            'total' => $total,
            'new_this_month' => $newThisMonth,
            'qualified' => $qualified,
            'conversion_rate' => $conversionRate,
        ];
    }

    public function assignTo(Lead $lead, int $userId): Lead
    {
        $lead->update(['assigned_to' => $userId]);

        return $lead->fresh();
    }

    public function changeStatus(Lead $lead, string $status): Lead
    {
        $lead->update(['status' => $status]);

        return $lead->fresh();
    }

    public function getByStatus(int $companyId, string $status): Collection
    {
        return Lead::where('company_id', $companyId)
            ->where('status', $status)
            ->with(['source', 'assignedUser'])
            ->get();
    }

    public function search(int $companyId, string $term): Collection
    {
        return Lead::where('company_id', $companyId)
            ->where(function ($query) use ($term) {
                $query->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%");
            })
            ->with(['source', 'assignedUser'])
            ->get();
    }
}
