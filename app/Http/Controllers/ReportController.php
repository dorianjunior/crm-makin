<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Services\Reports\ExportService;
use App\Services\Reports\ReportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    private ReportService $reportService;

    private ExportService $exportService;

    public function __construct(ReportService $reportService, ExportService $exportService)
    {
        $this->reportService = $reportService;
        $this->exportService = $exportService;
    }

    /**
     * List all reports
     */
    public function index(Request $request): JsonResponse
    {
        $query = Report::query()
            ->forCompany(auth()->user()->company_id)
            ->forUser(auth()->id())
            ->with(['user', 'schedules']);

        // Filters
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('is_favorite')) {
            $query->where('is_favorite', $request->boolean('is_favorite'));
        }

        if ($request->has('is_public')) {
            $query->where('is_public', $request->boolean('is_public'));
        }

        if ($request->has('search')) {
            $query->search($request->search);
        }

        $reports = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json($reports);
    }

    /**
     * Show a specific report
     */
    public function show(int $id): JsonResponse
    {
        $report = Report::query()
            ->forCompany(auth()->user()->company_id)
            ->forUser(auth()->id())
            ->with(['user', 'schedules', 'exports' => function ($query) {
                $query->latest()->limit(10);
            }])
            ->findOrFail($id);

        return response()->json($report);
    }

    /**
     * Create a new report
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:leads,sales,activities,tasks,proposals,pipeline,users,custom',
            'description' => 'nullable|string',
            'filters' => 'nullable|array',
            'columns' => 'nullable|array',
            'grouping' => 'nullable|array',
            'sorting' => 'nullable|array',
            'chart_config' => 'nullable|array',
            'is_public' => 'boolean',
            'is_favorite' => 'boolean',
        ]);

        $report = Report::create([
            ...$validated,
            'company_id' => auth()->user()->company_id,
            'user_id' => auth()->id(),
        ]);

        return response()->json([
            'message' => 'Relatório criado com sucesso',
            'report' => $report,
        ], 201);
    }

    /**
     * Update a report
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $report = Report::query()
            ->where('company_id', auth()->user()->company_id)
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        $validated = $request->validate([
            'name' => 'string|max:255',
            'type' => 'in:leads,sales,activities,tasks,proposals,pipeline,users,custom',
            'description' => 'nullable|string',
            'filters' => 'nullable|array',
            'columns' => 'nullable|array',
            'grouping' => 'nullable|array',
            'sorting' => 'nullable|array',
            'chart_config' => 'nullable|array',
            'is_public' => 'boolean',
            'is_favorite' => 'boolean',
        ]);

        $report->update($validated);

        return response()->json([
            'message' => 'Relatório atualizado com sucesso',
            'report' => $report->fresh(),
        ]);
    }

    /**
     * Delete a report
     */
    public function destroy(int $id): JsonResponse
    {
        $report = Report::query()
            ->where('company_id', auth()->user()->company_id)
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        $report->delete();

        return response()->json([
            'message' => 'Relatório deletado com sucesso',
        ]);
    }

    /**
     * Execute a report
     */
    public function execute(Request $request, int $id): JsonResponse
    {
        $report = Report::query()
            ->forCompany(auth()->user()->company_id)
            ->forUser(auth()->id())
            ->findOrFail($id);

        $filters = $request->get('filters', []);

        $results = $this->reportService->execute($report, $filters);

        return response()->json([
            'report' => $report,
            'results' => $results,
        ]);
    }

    /**
     * Export a report
     */
    public function export(Request $request, int $id): JsonResponse
    {
        $report = Report::query()
            ->forCompany(auth()->user()->company_id)
            ->forUser(auth()->id())
            ->findOrFail($id);

        $validated = $request->validate([
            'format' => 'required|in:pdf,excel,csv',
            'filters' => 'nullable|array',
        ]);

        $export = $this->exportService->export(
            $report,
            $validated['format'],
            auth()->id(),
            $validated['filters'] ?? []
        );

        return response()->json([
            'message' => 'Exportação iniciada com sucesso',
            'export' => $export,
        ], 202);
    }

    /**
     * Download an export
     */
    public function downloadExport(int $reportId, int $exportId): mixed
    {
        $export = $this->exportService->getExport($exportId, auth()->id());

        if (! $export || $export->report_id != $reportId) {
            return response()->json([
                'message' => 'Exportação não encontrada',
            ], 404);
        }

        try {
            $file = $this->exportService->download($export);

            return response()->download(
                $file['path'],
                $file['filename'],
                ['Content-Type' => $file['mime_type']]
            );
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Duplicate a report
     */
    public function duplicate(Request $request, int $id): JsonResponse
    {
        $report = Report::query()
            ->where('company_id', auth()->user()->company_id)
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        $name = $request->get('name');
        $duplicated = $report->duplicate($name);

        return response()->json([
            'message' => 'Relatório duplicado com sucesso',
            'report' => $duplicated,
        ], 201);
    }

    /**
     * Toggle favorite
     */
    public function toggleFavorite(int $id): JsonResponse
    {
        $report = Report::query()
            ->where('company_id', auth()->user()->company_id)
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        $isFavorite = $report->toggleFavorite();

        return response()->json([
            'message' => $isFavorite ? 'Adicionado aos favoritos' : 'Removido dos favoritos',
            'is_favorite' => $isFavorite,
        ]);
    }

    /**
     * Get available report types
     */
    public function types(): JsonResponse
    {
        return response()->json([
            'types' => Report::getAvailableTypes(),
        ]);
    }

    /**
     * Get available columns for a report type
     */
    public function columns(string $type): JsonResponse
    {
        return response()->json([
            'columns' => Report::getAvailableColumns($type),
        ]);
    }

    /**
     * Get available filters for a report type
     */
    public function filters(string $type): JsonResponse
    {
        return response()->json([
            'filters' => $this->reportService->getAvailableFilters($type),
        ]);
    }
}
