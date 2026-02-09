<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Services\CMS\SiteService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SiteController extends Controller
{
    public function __construct(
        private SiteService $siteService
    ) {}

    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $filters = [
            'search' => $request->get('search'),
            'status' => $request->get('status'),
        ];

        $sites = $this->siteService->getForIndex($companyId, $filters, 15);
        $stats = $this->siteService->getStats($companyId);

        return Inertia::render('CMS/Sites/Index', [
            'sites' => $sites,
            'stats' => $stats,
            'filters' => $filters,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'domain' => 'required|string|unique:sites,domain|max:255',
            'active' => 'boolean',
            'settings' => 'nullable|array',
        ]);

        $validated['company_id'] = auth()->user()->company_id;
        $validated['active'] = $validated['active'] ?? true;

        $this->siteService->create($validated);

        return redirect()->route('sites.index')
            ->with('success', 'Site criado com sucesso!');
    }

    public function update(Request $request, $id)
    {
        $companyId = auth()->user()->company_id;
        $site = $this->siteService->findByCompany($id, $companyId);

        if (! $site) {
            abort(404, 'Site não encontrado');
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'domain' => 'sometimes|string|max:255|unique:sites,domain,'.$id,
            'active' => 'sometimes|boolean',
            'settings' => 'nullable|array',
        ]);

        $this->siteService->update($site, $validated);

        return redirect()->route('sites.index')
            ->with('success', 'Site atualizado com sucesso!');
    }

    public function destroy($id)
    {
        $companyId = auth()->user()->company_id;
        $site = $this->siteService->findByCompany($id, $companyId);

        if (! $site) {
            abort(404, 'Site não encontrado');
        }

        $this->siteService->delete($site);

        return redirect()->route('sites.index')
            ->with('success', 'Site excluído com sucesso!');
    }

    public function regenerateApiKey($id)
    {
        $companyId = auth()->user()->company_id;
        $site = $this->siteService->findByCompany($id, $companyId);

        if (! $site) {
            abort(404, 'Site não encontrado');
        }

        $apiKey = $this->siteService->regenerateApiKey($site);

        return back()->with('success', 'API Key regenerada com sucesso!');
    }

    public function toggleActive($id)
    {
        $companyId = auth()->user()->company_id;
        $site = $this->siteService->findByCompany($id, $companyId);

        if (! $site) {
            abort(404, 'Site não encontrado');
        }

        if ($site->active) {
            $this->siteService->deactivate($site);
            $message = 'Site desativado com sucesso!';
        } else {
            $this->siteService->activate($site);
            $message = 'Site ativado com sucesso!';
        }

        return redirect()->route('sites.index')
            ->with('success', $message);
    }
}
