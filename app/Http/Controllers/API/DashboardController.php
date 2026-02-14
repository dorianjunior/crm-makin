<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\Reports\DashboardService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    private DashboardService $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Get main dashboard
     */
    public function index(Request $request): JsonResponse
    {
        $companyId = auth()->user()->company_id;

        $filters = [
            'date_from' => $request->get('date_from', Carbon::now()->startOfMonth()),
            'date_to' => $request->get('date_to', Carbon::now()->endOfMonth()),
        ];

        $dashboard = $this->dashboardService->getMainDashboard($companyId, $filters);

        return response()->json($dashboard);
    }

    /**
     * Get leads metrics
     */
    public function leads(Request $request): JsonResponse
    {
        $companyId = auth()->user()->company_id;
        $dateFrom = $request->get('date_from', Carbon::now()->startOfMonth());
        $dateTo = $request->get('date_to', Carbon::now()->endOfMonth());

        $metrics = $this->dashboardService->getLeadsMetrics($companyId, $dateFrom, $dateTo);

        return response()->json($metrics);
    }

    /**
     * Get sales metrics
     */
    public function sales(Request $request): JsonResponse
    {
        $companyId = auth()->user()->company_id;
        $dateFrom = $request->get('date_from', Carbon::now()->startOfMonth());
        $dateTo = $request->get('date_to', Carbon::now()->endOfMonth());

        $metrics = $this->dashboardService->getSalesMetrics($companyId, $dateFrom, $dateTo);

        return response()->json($metrics);
    }

    /**
     * Get activities metrics
     */
    public function activities(Request $request): JsonResponse
    {
        $companyId = auth()->user()->company_id;
        $dateFrom = $request->get('date_from', Carbon::now()->startOfMonth());
        $dateTo = $request->get('date_to', Carbon::now()->endOfMonth());

        $metrics = $this->dashboardService->getActivitiesMetrics($companyId, $dateFrom, $dateTo);

        return response()->json($metrics);
    }

    /**
     * Get tasks metrics
     */
    public function tasks(Request $request): JsonResponse
    {
        $companyId = auth()->user()->company_id;
        $dateFrom = $request->get('date_from', Carbon::now()->startOfMonth());
        $dateTo = $request->get('date_to', Carbon::now()->endOfMonth());

        $metrics = $this->dashboardService->getTasksMetrics($companyId, $dateFrom, $dateTo);

        return response()->json($metrics);
    }

    /**
     * Get conversion metrics
     */
    public function conversion(Request $request): JsonResponse
    {
        $companyId = auth()->user()->company_id;
        $dateFrom = $request->get('date_from', Carbon::now()->startOfMonth());
        $dateTo = $request->get('date_to', Carbon::now()->endOfMonth());

        $metrics = $this->dashboardService->getConversionMetrics($companyId, $dateFrom, $dateTo);

        return response()->json($metrics);
    }

    /**
     * Get top performers
     */
    public function topPerformers(Request $request): JsonResponse
    {
        $companyId = auth()->user()->company_id;
        $dateFrom = $request->get('date_from', Carbon::now()->startOfMonth());
        $dateTo = $request->get('date_to', Carbon::now()->endOfMonth());
        $limit = $request->get('limit', 5);

        $performers = $this->dashboardService->getTopPerformers($companyId, $dateFrom, $dateTo, $limit);

        return response()->json($performers);
    }

    /**
     * Get sales funnel
     */
    public function salesFunnel(Request $request): JsonResponse
    {
        $companyId = auth()->user()->company_id;
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        $funnel = $this->dashboardService->getSalesFunnel($companyId, $dateFrom, $dateTo);

        return response()->json($funnel);
    }

    /**
     * Get real-time metrics
     */
    public function realTime(): JsonResponse
    {
        $companyId = auth()->user()->company_id;

        $metrics = $this->dashboardService->getRealTimeMetrics($companyId);

        return response()->json($metrics);
    }
}
