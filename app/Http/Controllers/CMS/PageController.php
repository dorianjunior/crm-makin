<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Http\Requests\CMS\StorePageRequest;
use App\Http\Requests\CMS\UpdatePageRequest;
use App\Models\CMS\Page;
use App\Services\CMS\PageService;
use App\Services\CMS\SiteService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PageController extends Controller
{
    public function __construct(
        private PageService $pageService,
        private SiteService $siteService
    ) {}

    public function index(Request $request)
    {
        $companyId = auth()->user()->company_id;

        $filters = [
            'search' => $request->get('search'),
            'status' => $request->get('status'),
            'site_id' => $request->get('site_id'),
        ];

        // Get site_id from filter or get first site
        $siteId = $filters['site_id'];
        if (! $siteId) {
            $firstSite = $this->siteService->getByCompany($companyId)->first();
            $siteId = $firstSite?->id;
        }

        if (! $siteId) {
            return Inertia::render('CMS/Pages/Index', [
                'pages' => [],
                'stats' => [
                    'total' => 0,
                    'published' => 0,
                    'draft' => 0,
                    'pending' => 0,
                ],
                'sites' => [],
                'filters' => $filters,
            ]);
        }

        $pages = $this->pageService->getForIndex($siteId, $filters, 15);
        $stats = $this->pageService->getStats($siteId);
        $sites = $this->siteService->getByCompany($companyId);

        return Inertia::render('CMS/Pages/Index', [
            'pages' => $pages,
            'stats' => $stats,
            'sites' => $sites,
            'filters' => $filters,
        ]);
    }

    public function store(StorePageRequest $request)
    {
        $validated = $request->validated();

        $this->pageService->create($validated);

        return redirect()->route('pages.index')
            ->with('success', 'Página criada com sucesso!');
    }

    public function update(UpdatePageRequest $request, $id)
    {
        $companyId = auth()->user()->company_id;

        // Get site_id from request or find via page
        $page = Page::find($id);
        if (! $page) {
            abort(404, 'Página não encontrada');
        }

        // Verify page belongs to company's site
        $site = $this->siteService->findByCompany($page->site_id, $companyId);
        if (! $site) {
            abort(403, 'Você não tem permissão para editar esta página');
        }

        $validated = $request->validated();

        $this->pageService->update($page, $validated);

        return redirect()->route('pages.index')
            ->with('success', 'Página atualizada com sucesso!');
    }

    public function destroy($id)
    {
        $companyId = auth()->user()->company_id;

        $page = Page::find($id);
        if (! $page) {
            abort(404, 'Página não encontrada');
        }

        // Verify page belongs to company's site
        $site = $this->siteService->findByCompany($page->site_id, $companyId);
        if (! $site) {
            abort(403, 'Você não tem permissão para excluir esta página');
        }

        $this->pageService->delete($page);

        return redirect()->route('pages.index')
            ->with('success', 'Página excluída com sucesso!');
    }

    public function publish($id)
    {
        $companyId = auth()->user()->company_id;

        $page = Page::find($id);
        if (! $page) {
            abort(404, 'Página não encontrada');
        }

        // Verify page belongs to company's site
        $site = $this->siteService->findByCompany($page->site_id, $companyId);
        if (! $site) {
            abort(403, 'Você não tem permissão para publicar esta página');
        }

        $this->pageService->publish($page);

        return back()->with('success', 'Página publicada com sucesso!');
    }

    public function unpublish($id)
    {
        $companyId = auth()->user()->company_id;

        $page = Page::find($id);
        if (! $page) {
            abort(404, 'Página não encontrada');
        }

        // Verify page belongs to company's site
        $site = $this->siteService->findByCompany($page->site_id, $companyId);
        if (! $site) {
            abort(403, 'Você não tem permissão para despublicar esta página');
        }

        $this->pageService->unpublish($page);

        return back()->with('success', 'Página despublicada com sucesso!');
    }

    public function duplicate($id)
    {
        $companyId = auth()->user()->company_id;

        $page = Page::find($id);
        if (! $page) {
            abort(404, 'Página não encontrada');
        }

        // Verify page belongs to company's site
        $site = $this->siteService->findByCompany($page->site_id, $companyId);
        if (! $site) {
            abort(403, 'Você não tem permissão para duplicar esta página');
        }

        $this->pageService->duplicate($page);

        return back()->with('success', 'Página duplicada com sucesso!');
    }
}
