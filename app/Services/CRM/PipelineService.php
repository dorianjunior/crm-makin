<?php

namespace App\Services\CRM;

use App\Models\CRM\Pipeline;
use App\Models\CRM\PipelineStage;
use Illuminate\Support\Collection;

class PipelineService
{
    public function create(array $data): Pipeline
    {
        return Pipeline::create($data);
    }

    public function update(Pipeline $pipeline, array $data): Pipeline
    {
        $pipeline->update($data);

        return $pipeline->fresh();
    }

    public function delete(Pipeline $pipeline): bool
    {
        return $pipeline->delete();
    }

    public function getByCompany(int $companyId): Collection
    {
        return Pipeline::where('company_id', $companyId)
            ->with(['stages'])
            ->get();
    }

    public function addStage(Pipeline $pipeline, array $stageData): PipelineStage
    {
        $stageData['pipeline_id'] = $pipeline->id;

        return PipelineStage::create($stageData);
    }

    public function updateStage(PipelineStage $stage, array $data): PipelineStage
    {
        $stage->update($data);

        return $stage->fresh();
    }

    public function deleteStage(PipelineStage $stage): bool
    {
        return $stage->delete();
    }

    public function reorderStages(Pipeline $pipeline, array $stageOrders): bool
    {
        foreach ($stageOrders as $stageId => $order) {
            PipelineStage::where('id', $stageId)
                ->where('pipeline_id', $pipeline->id)
                ->update(['order' => $order]);
        }

        return true;
    }

    public function moveLead(int $leadId, int $stageId, int $position = 0): bool
    {
        $stage = PipelineStage::findOrFail($stageId);

        $stage->leads()->syncWithoutDetaching([
            $leadId => [
                'position' => $position,
                'entered_at' => now(),
            ],
        ]);

        return true;
    }
}
