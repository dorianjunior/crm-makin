<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CRM\Lead;
use App\Models\CRM\LeadSource;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $query = Lead::with(['source', 'assignedUser'])
            ->where('company_id', auth()->user()->company_id);

        // Filtros
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('company', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('source_id')) {
            $query->where('source_id', $request->source_id);
        }

        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        // Ordenação
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $leads = $query->paginate(15);

        // Estatísticas
        $companyId = auth()->user()->company_id;
        $stats = [
            'total' => Lead::where('company_id', $companyId)->count(),
            'new_this_month' => Lead::where('company_id', $companyId)
                ->where('status', 'new')
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'qualified' => Lead::where('company_id', $companyId)
                ->where('status', 'qualified')
                ->count(),
            'conversion_rate' => $this->calculateConversionRate($companyId),
        ];

        // Fontes e usuários para filtros
        $sources = LeadSource::where('company_id', $companyId)->get();
        $users = User::where('company_id', $companyId)->get();

        return Inertia::render('CRM/Leads/Index', [
            'leads' => $leads,
            'stats' => $stats,
            'sources' => $sources,
            'users' => $users,
            'filters' => $request->only(['search', 'status', 'source_id', 'assigned_to']),
        ]);
    }

    private function calculateConversionRate($companyId)
    {
        $total = Lead::where('company_id', $companyId)->count();
        if ($total === 0) {
            return 0;
        }

        $won = Lead::where('company_id', $companyId)
            ->where('status', 'won')
            ->count();

        return round(($won / $total) * 100, 1);
    }

    public function create()
    {
        $companyId = auth()->user()->company_id;
        $sources = LeadSource::where('company_id', $companyId)->get();
        $users = User::where('company_id', $companyId)->get();

        return Inertia::render('CRM/Leads/Create', [
            'sources' => $sources,
            'users' => $users,
        ]);
    }

    public function show(Lead $lead)
    {
        if ($lead->company_id !== auth()->user()->company_id) {
            abort(403, 'Acesso não autorizado');
        }

        $lead->load(['source', 'assignedUser']);

        return Inertia::render('CRM/Leads/Show', [
            'lead' => $lead,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'company' => 'nullable|string|max:255',
            'status' => 'required|in:new,contacted,qualified,negotiation,won,lost',
            'source_id' => 'nullable|exists:lead_sources,id',
            'assigned_to' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        $validated['company_id'] = auth()->user()->company_id;

        Lead::create($validated);

        return redirect()->route('leads.index')
            ->with('success', 'Lead criado com sucesso!');
    }

    public function edit(Lead $lead)
    {
        // Verifica se o lead pertence à empresa do usuário
        if ($lead->company_id !== auth()->user()->company_id) {
            abort(403, 'Acesso não autorizado');
        }

        $companyId = auth()->user()->company_id;
        $sources = LeadSource::where('company_id', $companyId)->get();
        $users = User::where('company_id', $companyId)->get();

        return Inertia::render('CRM/Leads/Edit', [
            'lead' => $lead,
            'sources' => $sources,
            'users' => $users,
        ]);
    }

    public function update(Request $request, Lead $lead)
    {
        // Verifica se o lead pertence à empresa do usuário
        if ($lead->company_id !== auth()->user()->company_id) {
            abort(403, 'Acesso não autorizado');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'company' => 'nullable|string|max:255',
            'status' => 'required|in:new,contacted,qualified,negotiation,won,lost',
            'source_id' => 'nullable|exists:lead_sources,id',
            'assigned_to' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        $lead->update($validated);

        return redirect()->route('leads.index')
            ->with('success', 'Lead atualizado com sucesso!');
    }

    public function destroy(Lead $lead)
    {
        // Verifica se o lead pertence à empresa do usuário
        if ($lead->company_id !== auth()->user()->company_id) {
            abort(403, 'Acesso não autorizado');
        }

        $lead->delete();

        return redirect()->route('leads.index')
            ->with('success', 'Lead excluído com sucesso!');
    }
}
