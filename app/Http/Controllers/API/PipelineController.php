<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\CRM\Pipeline;
use Illuminate\Http\Request;

class PipelineController extends Controller
{
    public function index(Request $request)
    {
        $query = Pipeline::with(['company', 'stages']);

        if ($request->has('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        $pipelines = $query->get();

        return response()->json($pipelines);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'name' => 'required|string|max:255',
        ]);

        $pipeline = Pipeline::create($validated);

        return response()->json($pipeline, 201);
    }

    public function show(Pipeline $pipeline)
    {
        return response()->json($pipeline->load(['company', 'stages.leads']));
    }

    public function update(Request $request, Pipeline $pipeline)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $pipeline->update($validated);

        return response()->json($pipeline);
    }

    public function destroy(Pipeline $pipeline)
    {
        $pipeline->delete();

        return response()->json(['message' => 'Pipeline deleted successfully']);
    }
}
