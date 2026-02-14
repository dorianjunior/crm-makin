<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\CRM\Lead;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $query = Lead::with(['company', 'source', 'assignedUser']);

        if ($request->has('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        $leads = $query->paginate(15);

        return response()->json($leads);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'source_id' => 'nullable|exists:lead_sources,id',
            'assigned_to' => 'nullable|exists:users,id',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'status' => 'required|in:new,contacted,qualified,proposal,negotiation,won,lost',
            'notes' => 'nullable|string',
        ]);

        $lead = Lead::create($validated);

        return response()->json($lead->load(['company', 'source', 'assignedUser']), 201);
    }

    public function show(Lead $lead)
    {
        return response()->json($lead->load([
            'company',
            'source',
            'assignedUser',
            'activities',
            'tasks',
            'proposals',
            'pipelineStages',
        ]));
    }

    public function update(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'source_id' => 'nullable|exists:lead_sources,id',
            'assigned_to' => 'nullable|exists:users,id',
            'name' => 'sometimes|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'status' => 'sometimes|in:new,contacted,qualified,proposal,negotiation,won,lost',
            'notes' => 'nullable|string',
        ]);

        $lead->update($validated);

        return response()->json($lead->load(['company', 'source', 'assignedUser']));
    }

    public function destroy(Lead $lead)
    {
        $lead->delete();

        return response()->json(['message' => 'Lead deleted successfully']);
    }
}
