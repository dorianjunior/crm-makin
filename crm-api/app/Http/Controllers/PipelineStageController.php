<?php

namespace App\Http\Controllers;

use App\Models\PipelineStage;
use Illuminate\Http\Request;

class PipelineStageController extends Controller
{
    public function index(Request $request)
    {
        $query = PipelineStage::with('pipeline');

        if ($request->has('pipeline_id')) {
            $query->where('pipeline_id', $request->pipeline_id);
        }

        $stages = $query->orderBy('order')->get();
        return response()->json($stages);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pipeline_id' => 'required|exists:pipelines,id',
            'name' => 'required|string|max:255',
            'order' => 'required|integer|min:0',
        ]);

        $stage = PipelineStage::create($validated);
        return response()->json($stage, 201);
    }

    public function show(PipelineStage $pipelineStage)
    {
        return response()->json($pipelineStage->load(['pipeline', 'leads']));
    }

    public function update(Request $request, PipelineStage $pipelineStage)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'order' => 'sometimes|integer|min:0',
        ]);

        $pipelineStage->update($validated);
        return response()->json($pipelineStage);
    }

    public function destroy(PipelineStage $pipelineStage)
    {
        $pipelineStage->delete();
        return response()->json(['message' => 'Pipeline stage deleted successfully']);
    }

    public function attachLead(Request $request, PipelineStage $pipelineStage)
    {
        $validated = $request->validate([
            'lead_id' => 'required|exists:leads,id',
            'position' => 'integer|min:0',
        ]);

        $pipelineStage->leads()->attach($validated['lead_id'], [
            'position' => $validated['position'] ?? 0,
        ]);

        return response()->json(['message' => 'Lead attached to stage successfully']);
    }

    public function detachLead(PipelineStage $pipelineStage, $leadId)
    {
        $pipelineStage->leads()->detach($leadId);
        return response()->json(['message' => 'Lead detached from stage successfully']);
    }
}
