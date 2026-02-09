<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CRM\Pipeline;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PipelineController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $companyId = auth()->user()->company_id;

        $pipelines = Pipeline::where('company_id', $companyId)
            ->with(['stages' => function ($query) {
                $query->withCount('leads');
            }])
            ->get()
            ->map(function ($pipeline) {
                return [
                    'id' => $pipeline->id,
                    'name' => $pipeline->name,
                    'description' => $pipeline->description,
                    'is_active' => $pipeline->is_active,
                    'is_default' => $pipeline->is_default,
                    'stages_count' => $pipeline->stages_count,
                    'leads_count' => $pipeline->leads_count,
                    'stages' => $pipeline->stages->map(function ($stage) {
                        return [
                            'id' => $stage->id,
                            'pipeline_id' => $stage->pipeline_id,
                            'name' => $stage->name,
                            'order' => $stage->order,
                            'probability' => $stage->probability,
                            'color' => $stage->color,
                            'leads_count' => $stage->leads_count ?? 0,
                        ];
                    }),
                ];
            });

        return Inertia::render('CRM/Pipelines/Index', [
            'pipelines' => $pipelines,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ]);

        $validated['company_id'] = auth()->user()->company_id;

        Pipeline::create($validated);

        return redirect()->route('pipelines.index')
            ->with('success', 'Pipeline criado com sucesso!');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Pipeline $pipeline)
    {
        // Verificar se o pipeline pertence à empresa do usuário
        if ($pipeline->company_id !== auth()->user()->company_id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
            'is_default' => 'sometimes|boolean',
        ]);

        $pipeline->update($validated);

        return redirect()->route('pipelines.index')
            ->with('success', 'Pipeline atualizado com sucesso!');
    }

    /**
     * Partially update the specified resource in storage.
     */
    public function patch(Request $request, Pipeline $pipeline)
    {
        // Verificar se o pipeline pertence à empresa do usuário
        if ($pipeline->company_id !== auth()->user()->company_id) {
            abort(403);
        }

        $validated = $request->validate([
            'is_active' => 'sometimes|boolean',
            'is_default' => 'sometimes|boolean',
        ]);

        $pipeline->update($validated);

        return redirect()->route('pipelines.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Pipeline $pipeline)
    {
        // Verificar se o pipeline pertence à empresa do usuário
        if ($pipeline->company_id !== auth()->user()->company_id) {
            abort(403);
        }

        // Não permitir deletar pipeline padrão
        if ($pipeline->is_default) {
            return redirect()->route('pipelines.index')
                ->with('error', 'Não é possível excluir o pipeline padrão.');
        }

        // Verificar se há um pipeline padrão para mover os leads
        $defaultPipeline = Pipeline::where('company_id', $pipeline->company_id)
            ->where('is_default', true)
            ->first();

        if (! $defaultPipeline) {
            return redirect()->route('pipelines.index')
                ->with('error', 'É necessário ter um pipeline padrão antes de excluir este.');
        }

        // Mover leads para o primeiro estágio do pipeline padrão
        $firstStage = $defaultPipeline->stages()->orderBy('order')->first();

        if ($firstStage) {
            foreach ($pipeline->stages as $stage) {
                $leadIds = $stage->leads()->pluck('leads.id')->toArray();

                if (! empty($leadIds)) {
                    foreach ($leadIds as $leadId) {
                        $firstStage->leads()->syncWithoutDetaching([
                            $leadId => [
                                'position' => 0,
                                'entered_at' => now(),
                            ],
                        ]);
                    }
                }
            }
        }

        $pipeline->delete();

        return redirect()->route('pipelines.index')
            ->with('success', 'Pipeline excluído com sucesso!');
    }

    /**
     * Set pipeline as default.
     */
    public function setDefault(Pipeline $pipeline)
    {
        // Verificar se o pipeline pertence à empresa do usuário
        if ($pipeline->company_id !== auth()->user()->company_id) {
            abort(403);
        }

        $pipeline->update(['is_default' => true]);

        return redirect()->route('pipelines.index')
            ->with('success', 'Pipeline definido como padrão!');
    }

    /**
     * Reorder pipeline stages.
     */
    public function reorderStages(Request $request, Pipeline $pipeline)
    {
        // Verificar se o pipeline pertence à empresa do usuário
        if ($pipeline->company_id !== auth()->user()->company_id) {
            abort(403);
        }

        $validated = $request->validate([
            'stages' => 'required|array',
            'stages.*.id' => 'required|exists:pipeline_stages,id',
            'stages.*.order' => 'required|integer|min:1',
        ]);

        foreach ($validated['stages'] as $stageData) {
            $pipeline->stages()
                ->where('id', $stageData['id'])
                ->update(['order' => $stageData['order']]);
        }

        return redirect()->route('pipelines.index')
            ->with('success', 'Ordem dos estágios atualizada!');
    }
}
