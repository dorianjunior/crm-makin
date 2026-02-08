<?php

namespace App\Http\Controllers;

use App\Services\CRM\ProposalService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProposalController extends Controller
{
    public function __construct(
        private ProposalService $proposalService
    ) {}

    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $filters = [
            'search' => $request->get('search'),
            'status' => $request->get('status'),
            'period' => $request->get('period'),
        ];

        $proposals = $this->proposalService->getForIndex($companyId, $filters, 15);
        $stats = $this->proposalService->getStats($companyId);

        return Inertia::render('Proposals/Index', [
            'proposals' => $proposals,
            'stats' => $stats,
            'filters' => $filters,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'lead_id' => 'required|exists:leads,id',
            'status' => 'required|in:draft,sent,viewed,approved,rejected',
            'notes' => 'nullable|string',
            'valid_until' => 'nullable|date',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0|max:100',
        ]);

        $companyId = auth()->user()->company_id;
        $proposal = $this->proposalService->create($companyId, $validated);

        return redirect()->route('proposals.index')
            ->with('success', 'Proposta criada com sucesso!');
    }

    public function update(Request $request, $id)
    {
        $companyId = auth()->user()->company_id;
        $proposal = $this->proposalService->findByCompany($id, $companyId);

        if (!$proposal) {
            abort(404, 'Proposta não encontrada');
        }

        $validated = $request->validate([
            'status' => 'sometimes|in:draft,sent,viewed,approved,rejected',
            'notes' => 'nullable|string',
            'valid_until' => 'nullable|date',
            'items' => 'sometimes|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0|max:100',
        ]);

        $this->proposalService->update($proposal, $validated);

        return redirect()->route('proposals.index')
            ->with('success', 'Proposta atualizada com sucesso!');
    }

    public function destroy($id)
    {
        $companyId = auth()->user()->company_id;
        $proposal = $this->proposalService->findByCompany($id, $companyId);

        if (!$proposal) {
            abort(404, 'Proposta não encontrada');
        }

        $this->proposalService->delete($proposal);

        return redirect()->route('proposals.index')
            ->with('success', 'Proposta excluída com sucesso!');
    }

    public function duplicate($id)
    {
        $companyId = auth()->user()->company_id;
        $proposal = $this->proposalService->findByCompany($id, $companyId);

        if (!$proposal) {
            abort(404, 'Proposta não encontrada');
        }

        $this->proposalService->duplicate($proposal);

        return redirect()->route('proposals.index')
            ->with('success', 'Proposta duplicada com sucesso!');
    }

    public function send($id)
    {
        $companyId = auth()->user()->company_id;
        $proposal = $this->proposalService->findByCompany($id, $companyId);

        if (!$proposal) {
            abort(404, 'Proposta não encontrada');
        }

        $this->proposalService->markAsSent($proposal);

        return redirect()->route('proposals.index')
            ->with('success', 'Proposta enviada com sucesso!');
    }

    public function download($id)
    {
        $companyId = auth()->user()->company_id;
        $proposal = $this->proposalService->findByCompany($id, $companyId);

        if (!$proposal) {
            abort(404, 'Proposta não encontrada');
        }

        // TODO: Implementar geração de PDF
        return response()->json(['message' => 'Download em desenvolvimento']);
    }
}
