<?php

namespace App\Http\Controllers;

use App\Models\CRM\Proposal;
use Illuminate\Http\Request;

class ProposalController extends Controller
{
    public function index(Request $request)
    {
        $query = Proposal::with(['lead', 'items.product']);

        if ($request->has('lead_id')) {
            $query->where('lead_id', $request->lead_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $proposals = $query->paginate(15);

        return response()->json($proposals);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'lead_id' => 'required|exists:leads,id',
            'status' => 'required|in:draft,sent,accepted,rejected',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        $totalValue = collect($validated['items'])->sum(function ($item) {
            return $item['quantity'] * $item['price'];
        });

        $proposal = Proposal::create([
            'lead_id' => $validated['lead_id'],
            'total_value' => $totalValue,
            'status' => $validated['status'],
        ]);

        foreach ($validated['items'] as $item) {
            $proposal->items()->create($item);
        }

        return response()->json($proposal->load(['lead', 'items.product']), 201);
    }

    public function show(Proposal $proposal)
    {
        return response()->json($proposal->load(['lead', 'items.product']));
    }

    public function update(Request $request, Proposal $proposal)
    {
        $validated = $request->validate([
            'status' => 'sometimes|in:draft,sent,accepted,rejected',
            'items' => 'sometimes|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        if (isset($validated['status'])) {
            $proposal->update(['status' => $validated['status']]);
        }

        if (isset($validated['items'])) {
            $proposal->items()->delete();

            $totalValue = collect($validated['items'])->sum(function ($item) {
                return $item['quantity'] * $item['price'];
            });

            foreach ($validated['items'] as $item) {
                $proposal->items()->create($item);
            }

            $proposal->update(['total_value' => $totalValue]);
        }

        return response()->json($proposal->load(['lead', 'items.product']));
    }

    public function destroy(Proposal $proposal)
    {
        $proposal->delete();

        return response()->json(['message' => 'Proposal deleted successfully']);
    }
}
