<?php

namespace App\Services\CRM;

use App\Models\CRM\Proposal;
use App\Models\CRM\ProposalItem;
use Illuminate\Support\Collection;

class ProposalService
{
    public function create(array $data): Proposal
    {
        $proposal = Proposal::create([
            'lead_id' => $data['lead_id'],
            'status' => $data['status'] ?? 'draft',
            'total_value' => 0,
        ]);

        if (isset($data['items'])) {
            foreach ($data['items'] as $item) {
                $this->addItem($proposal, $item);
            }
        }

        return $proposal->fresh(['items', 'lead']);
    }

    public function update(Proposal $proposal, array $data): Proposal
    {
        $proposal->update([
            'status' => $data['status'] ?? $proposal->status,
        ]);

        if (isset($data['items'])) {
            // Remove existing items
            $proposal->items()->delete();

            // Add new items
            foreach ($data['items'] as $item) {
                $this->addItem($proposal, $item);
            }
        }

        return $proposal->fresh(['items', 'lead']);
    }

    public function delete(Proposal $proposal): bool
    {
        return $proposal->delete();
    }

    public function addItem(Proposal $proposal, array $itemData): ProposalItem
    {
        $item = ProposalItem::create([
            'proposal_id' => $proposal->id,
            'product_id' => $itemData['product_id'],
            'quantity' => $itemData['quantity'],
            'price' => $itemData['price'],
        ]);

        $this->recalculateTotal($proposal);

        return $item;
    }

    public function removeItem(ProposalItem $item): bool
    {
        $proposal = $item->proposal;
        $deleted = $item->delete();

        if ($deleted) {
            $this->recalculateTotal($proposal);
        }

        return $deleted;
    }

    public function recalculateTotal(Proposal $proposal): void
    {
        $total = $proposal->items()
            ->get()
            ->sum(function ($item) {
                return $item->quantity * $item->price;
            });

        $proposal->update(['total_value' => $total]);
    }

    public function markAsAccepted(Proposal $proposal): Proposal
    {
        $proposal->update(['status' => 'accepted']);

        return $proposal->fresh();
    }

    public function markAsRejected(Proposal $proposal): Proposal
    {
        $proposal->update(['status' => 'rejected']);

        return $proposal->fresh();
    }

    public function getByLead(int $leadId): Collection
    {
        return Proposal::where('lead_id', $leadId)
            ->with(['items.product'])
            ->get();
    }

    public function getForIndex(int $companyId, array $filters = [], int $perPage = 15)
    {
        $query = Proposal::with(['lead.company', 'items.product'])
            ->whereHas('lead', function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            })
            ->orderBy('created_at', 'desc');

        // Search filter
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('number', 'like', "%{$search}%")
                    ->orWhereHas('lead', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Status filter
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Period filter
        if (!empty($filters['period'])) {
            $this->applyPeriodFilter($query, $filters['period']);
        }

        return $query->paginate($perPage);
    }

    public function getStats(int $companyId): array
    {
        $baseQuery = Proposal::whereHas('lead', function ($q) use ($companyId) {
            $q->where('company_id', $companyId);
        });

        return [
            'total' => (clone $baseQuery)->count(),
            'draft' => (clone $baseQuery)->where('status', 'draft')->count(),
            'sent' => (clone $baseQuery)->where('status', 'sent')->count(),
            'approved' => (clone $baseQuery)->where('status', 'approved')->count(),
            'rejected' => (clone $baseQuery)->where('status', 'rejected')->count(),
            'total_value' => (clone $baseQuery)->sum('total_value'),
        ];
    }

    public function findByCompany(int $id, int $companyId)
    {
        return Proposal::with(['lead', 'items.product'])
            ->whereHas('lead', function ($q) use ($companyId) {
                $q->where('company_id', $companyId);
            })
            ->find($id);
    }

    public function duplicate(Proposal $proposal): Proposal
    {
        $newProposal = $proposal->replicate();
        $newProposal->number = $this->generateProposalNumber();
        $newProposal->status = 'draft';
        $newProposal->save();

        foreach ($proposal->items as $item) {
            $newItem = $item->replicate();
            $newItem->proposal_id = $newProposal->id;
            $newItem->save();
        }

        return $newProposal->load(['lead', 'items.product']);
    }

    public function markAsSent(Proposal $proposal): Proposal
    {
        $proposal->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        return $proposal->fresh();
    }

    private function applyPeriodFilter($query, string $period): void
    {
        switch ($period) {
            case 'today':
                $query->whereDate('created_at', today());
                break;
            case 'week':
                $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
                break;
            case 'month':
                $query->whereMonth('created_at', now()->month);
                break;
            case 'year':
                $query->whereYear('created_at', now()->year);
                break;
        }
    }

    private function generateProposalNumber(): string
    {
        $lastProposal = Proposal::orderBy('id', 'desc')->first();
        $nextNumber = $lastProposal ? ((int) substr($lastProposal->number, 4)) + 1 : 1;
        return 'PROP' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }
}
