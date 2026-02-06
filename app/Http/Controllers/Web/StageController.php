<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CRM\Pipeline;
use App\Models\CRM\PipelineStage;
use Illuminate\Http\Request;

class StageController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'pipeline_id' => 'required|exists:pipelines,id',
            'name' => 'required|string|max:255',
            'probability' => 'required|integer|min:0|max:100',
            'color' => 'required|string|max:7',
        ]);

        // Verificar se o pipeline pertence à empresa do usuário
        $pipeline = Pipeline::findOrFail($validated['pipeline_id']);
        if ($pipeline->company_id !== auth()->user()->company_id) {
            abort(403);
        }

        // Obter o próximo order
        $maxOrder = PipelineStage::where('pipeline_id', $validated['pipeline_id'])->max('order');
        $validated['order'] = ($maxOrder ?? 0) + 1;

        PipelineStage::create($validated);

        return redirect()->route('pipelines.index')
            ->with('success', 'Estágio criado com sucesso!');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PipelineStage $stage)
    {
        // Verificar se o pipeline do stage pertence à empresa do usuário
        if ($stage->pipeline->company_id !== auth()->user()->company_id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'probability' => 'sometimes|integer|min:0|max:100',
            'color' => 'sometimes|string|max:7',
        ]);

        $stage->update($validated);

        return redirect()->route('pipelines.index')
            ->with('success', 'Estágio atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PipelineStage $stage)
    {
        // Verificar se o pipeline do stage pertence à empresa do usuário
        if ($stage->pipeline->company_id !== auth()->user()->company_id) {
            abort(403);
        }

        // Mover leads para o primeiro estágio do mesmo pipeline
        $firstStage = $stage->pipeline->stages()
            ->where('id', '!=', $stage->id)
            ->orderBy('order')
            ->first();

        if ($firstStage) {
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

        $stage->delete();

        return redirect()->route('pipelines.index')
            ->with('success', 'Estágio excluído com sucesso!');
    }
}
