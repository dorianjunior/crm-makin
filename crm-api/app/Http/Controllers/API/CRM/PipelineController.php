<?php

namespace App\Http\Controllers\API\CRM;

use App\Http\Controllers\Controller;
use App\Http\Requests\CRM\StorePipelineRequest;
use App\Http\Requests\CRM\UpdatePipelineRequest;
use App\Http\Resources\CRM\PipelineResource;
use App\Models\CRM\Pipeline;
use App\Services\CRM\PipelineService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class PipelineController extends Controller
{
    public function __construct(
        private readonly PipelineService $pipelineService
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Pipeline::with('stages');

        if ($request->has('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        $pipelines = $query->get();

        return PipelineResource::collection($pipelines);
    }

    public function store(StorePipelineRequest $request): PipelineResource
    {
        $pipeline = $this->pipelineService->create($request->validated());

        return new PipelineResource($pipeline->load('stages'));
    }

    public function show(Pipeline $pipeline): PipelineResource
    {
        $pipeline->load('stages.leads');

        return new PipelineResource($pipeline);
    }

    public function update(UpdatePipelineRequest $request, Pipeline $pipeline): PipelineResource
    {
        $pipeline = $this->pipelineService->update($pipeline, $request->validated());

        return new PipelineResource($pipeline->load('stages'));
    }

    public function destroy(Pipeline $pipeline): Response
    {
        $this->pipelineService->delete($pipeline);

        return response()->noContent();
    }

    public function addStage(Request $request, Pipeline $pipeline): PipelineResource
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'order' => 'nullable|integer|min:0',
        ]);

        $pipeline = $this->pipelineService->addStage(
            $pipeline,
            $request->input('name'),
            $request->input('order')
        );

        return new PipelineResource($pipeline->load('stages'));
    }

    public function updateStage(Request $request, Pipeline $pipeline, int $stageId): PipelineResource
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'order' => 'sometimes|integer|min:0',
        ]);

        $pipeline = $this->pipelineService->updateStage($stageId, $request->only(['name', 'order']));

        return new PipelineResource($pipeline->load('stages'));
    }

    public function reorderStages(Request $request, Pipeline $pipeline): PipelineResource
    {
        $request->validate([
            'stages' => 'required|array',
            'stages.*.id' => 'required|exists:pipeline_stages,id',
            'stages.*.order' => 'required|integer|min:0',
        ]);

        $pipeline = $this->pipelineService->reorderStages($request->input('stages'));

        return new PipelineResource($pipeline->load('stages'));
    }
}
