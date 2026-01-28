<?php

namespace App\Services\CRM;

use App\Models\CRM\Lead;
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

    public function getByCompany(int $companyId, array $filters = []): Collection
    {
        $query = Lead::where('company_id', $companyId);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['assigned_to'])) {
            $query->where('assigned_to', $filters['assigned_to']);
        }

        if (isset($filters['source_id'])) {
            $query->where('source_id', $filters['source_id']);
        }

        return $query->with(['company', 'source', 'assignedUser'])->get();
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
