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
                    ->orWhere('phone', 'like', "%{$search}%");
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
                ->count(),
            'qualified' => Lead::where('company_id', $companyId)
                ->where('status', 'qualified')
                ->count(),
            'conversion_rate' => $this->calculateConversionRate($companyId),
        ];

        // Fontes e usuários para filtros
        $sources = LeadSource::where('company_id', $companyId)->get();
        $users = User::where('company_id', $companyId)->get();

        return Inertia::render('Leads/Index', [
            'leads' => $leads,
            'stats' => $stats,
            'sources' => $sources,
            'users' => $users,
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
}
