<?php

namespace App\Http\Controllers;

use App\Models\CRM\LeadSource;
use Illuminate\Http\Request;

class LeadSourceController extends Controller
{
    public function index(Request $request)
    {
        $query = LeadSource::with('company');

        if ($request->has('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        $sources = $query->get();

        return response()->json($sources);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'name' => 'required|string|max:255',
        ]);

        $source = LeadSource::create($validated);

        return response()->json($source, 201);
    }

    public function show(LeadSource $leadSource)
    {
        return response()->json($leadSource->load(['company', 'leads']));
    }

    public function update(Request $request, LeadSource $leadSource)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $leadSource->update($validated);

        return response()->json($leadSource);
    }

    public function destroy(LeadSource $leadSource)
    {
        $leadSource->delete();

        return response()->json(['message' => 'Lead source deleted successfully']);
    }
}
