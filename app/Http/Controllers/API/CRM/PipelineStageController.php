<?php

namespace App\Http\Controllers\API\CRM;

use App\Http\Controllers\Controller;
use App\Http\Requests\CRM\StorePipelineStageRequest;
use App\Http\Requests\CRM\UpdatePipelineStageRequest;
use App\Http\Resources\CRM\PipelineStageResource;
use App\Models\CRM\PipelineStage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class PipelineStageController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = PipelineStage::with('pipeline');

        if ($request->has('pipeline_id')) {
            $query->where('pipeline_id', $request->pipeline_id);
        }

        $stages = $query->orderBy('order')->withCount('leads')->get();

        return PipelineStageResource::collection($stages);
    }

    public function store(StorePipelineStageRequest $request): PipelineStageResource
    {
        $stage = PipelineStage::create($request->validated());

        return new PipelineStageResource($stage);
    }

    public function show(PipelineStage $pipelineStage): PipelineStageResource
    {
        $pipelineStage->loadCount('leads');

        return new PipelineStageResource($pipelineStage);
    }

    public function update(UpdatePipelineStageRequest $request, PipelineStage $pipelineStage): PipelineStageResource
    {
        $pipelineStage->update($request->validated());

        return new PipelineStageResource($pipelineStage);
    }

    public function destroy(PipelineStage $pipelineStage): Response
    {
        $pipelineStage->delete();

        return response()->noContent();
    }
}
