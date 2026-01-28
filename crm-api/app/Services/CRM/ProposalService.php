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
}
