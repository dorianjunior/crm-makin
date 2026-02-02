<?php

namespace App\Services\Reports;

use App\Models\Activity;
use App\Models\Lead;
use App\Models\Proposal;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    /**
     * Get main dashboard metrics
     */
    public function getMainDashboard(int $companyId, array $filters = []): array
    {
        $dateFrom = $filters['date_from'] ?? Carbon::now()->startOfMonth();
        $dateTo = $filters['date_to'] ?? Carbon::now()->endOfMonth();

        return [
            'leads' => $this->getLeadsMetrics($companyId, $dateFrom, $dateTo),
            'sales' => $this->getSalesMetrics($companyId, $dateFrom, $dateTo),
            'activities' => $this->getActivitiesMetrics($companyId, $dateFrom, $dateTo),
            'tasks' => $this->getTasksMetrics($companyId, $dateFrom, $dateTo),
            'conversion' => $this->getConversionMetrics($companyId, $dateFrom, $dateTo),
            'top_performers' => $this->getTopPerformers($companyId, $dateFrom, $dateTo),
        ];
    }

    /**
     * Get leads metrics
     */
    public function getLeadsMetrics(int $companyId, $dateFrom, $dateTo): array
    {
        $leads = Lead::where('company_id', $companyId)
            ->whereBetween('created_at', [$dateFrom, $dateTo]);

        $previousPeriod = Lead::where('company_id', $companyId)
            ->whereBetween('created_at', [
                Carbon::parse($dateFrom)->subDays($dateTo->diffInDays($dateFrom)),
                $dateFrom,
            ]);

        $total = $leads->count();
        $previousTotal = $previousPeriod->count();
        $growth = $previousTotal > 0 ? (($total - $previousTotal) / $previousTotal) * 100 : 0;

        return [
            'total' => $total,
            'new' => (clone $leads)->where('status', 'new')->count(),
            'qualified' => (clone $leads)->where('status', 'qualified')->count(),
            'converted' => (clone $leads)->where('status', 'converted')->count(),
            'lost' => (clone $leads)->where('status', 'lost')->count(),
            'total_value' => (clone $leads)->sum('value'),
            'avg_value' => (clone $leads)->avg('value'),
            'growth_percentage' => round($growth, 2),
            'by_source' => (clone $leads)->select('lead_source_id', DB::raw('count(*) as total'))
                ->groupBy('lead_source_id')
                ->with('leadSource')
                ->get(),
            'timeline' => $this->getTimelineData($leads, $dateFrom, $dateTo),
        ];
    }

    /**
     * Get sales metrics
     */
    public function getSalesMetrics(int $companyId, $dateFrom, $dateTo): array
    {
        $sales = Proposal::where('company_id', $companyId)
            ->where('status', 'accepted')
            ->whereBetween('accepted_at', [$dateFrom, $dateTo]);

        $previousPeriod = Proposal::where('company_id', $companyId)
            ->where('status', 'accepted')
            ->whereBetween('accepted_at', [
                Carbon::parse($dateFrom)->subDays($dateTo->diffInDays($dateFrom)),
                $dateFrom,
            ]);

        $totalRevenue = $sales->sum('total');
        $previousRevenue = $previousPeriod->sum('total');
        $growth = $previousRevenue > 0 ? (($totalRevenue - $previousRevenue) / $previousRevenue) * 100 : 0;

        return [
            'total_sales' => $sales->count(),
            'total_revenue' => $totalRevenue,
            'avg_ticket' => $sales->avg('total'),
            'growth_percentage' => round($growth, 2),
            'by_user' => (clone $sales)->select('user_id', DB::raw('count(*) as total'), DB::raw('sum(total) as revenue'))
                ->groupBy('user_id')
                ->with('user')
                ->get(),
            'timeline' => $this->getTimelineData($sales, $dateFrom, $dateTo, 'accepted_at', 'total'),
        ];
    }

    /**
     * Get activities metrics
     */
    public function getActivitiesMetrics(int $companyId, $dateFrom, $dateTo): array
    {
        $activities = Activity::whereHas('user', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })->whereBetween('created_at', [$dateFrom, $dateTo]);

        return [
            'total' => $activities->count(),
            'by_type' => (clone $activities)->select('type', DB::raw('count(*) as total'))
                ->groupBy('type')
                ->get(),
            'by_user' => (clone $activities)->select('user_id', DB::raw('count(*) as total'))
                ->groupBy('user_id')
                ->with('user')
                ->orderBy('total', 'desc')
                ->limit(10)
                ->get(),
            'timeline' => $this->getTimelineData($activities, $dateFrom, $dateTo),
        ];
    }

    /**
     * Get tasks metrics
     */
    public function getTasksMetrics(int $companyId, $dateFrom, $dateTo): array
    {
        $tasks = Task::whereHas('assignedTo', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })->whereBetween('created_at', [$dateFrom, $dateTo]);

        $overdue = Task::whereHas('assignedTo', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })->where('status', '!=', 'completed')
            ->where('due_date', '<', now());

        return [
            'total' => $tasks->count(),
            'pending' => (clone $tasks)->where('status', 'pending')->count(),
            'in_progress' => (clone $tasks)->where('status', 'in_progress')->count(),
            'completed' => (clone $tasks)->where('status', 'completed')->count(),
            'overdue' => $overdue->count(),
            'completion_rate' => $tasks->count() > 0
                ? round(((clone $tasks)->where('status', 'completed')->count() / $tasks->count()) * 100, 2)
                : 0,
            'by_priority' => (clone $tasks)->select('priority', DB::raw('count(*) as total'))
                ->groupBy('priority')
                ->get(),
        ];
    }

    /**
     * Get conversion metrics
     */
    public function getConversionMetrics(int $companyId, $dateFrom, $dateTo): array
    {
        $totalLeads = Lead::where('company_id', $companyId)
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->count();

        $convertedLeads = Lead::where('company_id', $companyId)
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->where('status', 'converted')
            ->count();

        $lostLeads = Lead::where('company_id', $companyId)
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->where('status', 'lost')
            ->count();

        $conversionRate = $totalLeads > 0 ? ($convertedLeads / $totalLeads) * 100 : 0;
        $lossRate = $totalLeads > 0 ? ($lostLeads / $totalLeads) * 100 : 0;

        return [
            'total_leads' => $totalLeads,
            'converted_leads' => $convertedLeads,
            'lost_leads' => $lostLeads,
            'conversion_rate' => round($conversionRate, 2),
            'loss_rate' => round($lossRate, 2),
        ];
    }

    /**
     * Get top performers
     */
    public function getTopPerformers(int $companyId, $dateFrom, $dateTo, int $limit = 5): array
    {
        $topSellers = Proposal::where('company_id', $companyId)
            ->where('status', 'accepted')
            ->whereBetween('accepted_at', [$dateFrom, $dateTo])
            ->select('user_id', DB::raw('count(*) as sales_count'), DB::raw('sum(total) as total_revenue'))
            ->groupBy('user_id')
            ->with('user')
            ->orderBy('total_revenue', 'desc')
            ->limit($limit)
            ->get();

        $topLeadGenerators = Lead::where('company_id', $companyId)
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->select('assigned_to_user_id', DB::raw('count(*) as leads_count'))
            ->groupBy('assigned_to_user_id')
            ->with('user')
            ->orderBy('leads_count', 'desc')
            ->limit($limit)
            ->get();

        return [
            'top_sellers' => $topSellers,
            'top_lead_generators' => $topLeadGenerators,
        ];
    }

    /**
     * Get sales funnel data
     */
    public function getSalesFunnel(int $companyId, $dateFrom = null, $dateTo = null): array
    {
        $dateFrom = $dateFrom ?? Carbon::now()->startOfMonth();
        $dateTo = $dateTo ?? Carbon::now()->endOfMonth();

        $leads = Lead::where('company_id', $companyId)
            ->whereBetween('created_at', [$dateFrom, $dateTo]);

        $contacted = (clone $leads)->whereIn('status', ['contacted', 'qualified', 'converted'])->count();
        $qualified = (clone $leads)->whereIn('status', ['qualified', 'converted'])->count();
        $proposalSent = Proposal::where('company_id', $companyId)
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->whereIn('status', ['sent', 'accepted'])
            ->count();
        $converted = (clone $leads)->where('status', 'converted')->count();

        $totalLeads = $leads->count();

        return [
            'stages' => [
                ['name' => 'Total Leads', 'count' => $totalLeads, 'percentage' => 100],
                ['name' => 'Contactados', 'count' => $contacted, 'percentage' => $totalLeads > 0 ? round(($contacted / $totalLeads) * 100, 2) : 0],
                ['name' => 'Qualificados', 'count' => $qualified, 'percentage' => $totalLeads > 0 ? round(($qualified / $totalLeads) * 100, 2) : 0],
                ['name' => 'Proposta Enviada', 'count' => $proposalSent, 'percentage' => $totalLeads > 0 ? round(($proposalSent / $totalLeads) * 100, 2) : 0],
                ['name' => 'Convertidos', 'count' => $converted, 'percentage' => $totalLeads > 0 ? round(($converted / $totalLeads) * 100, 2) : 0],
            ],
        ];
    }

    /**
     * Get timeline data for charts
     */
    private function getTimelineData($query, $dateFrom, $dateTo, string $dateField = 'created_at', ?string $valueField = null)
    {
        $days = Carbon::parse($dateFrom)->diffInDays($dateTo);

        $format = $days > 60 ? '%Y-%m' : '%Y-%m-%d';

        $timeline = (clone $query)
            ->select(
                DB::raw("DATE_FORMAT({$dateField}, '{$format}') as date"),
                DB::raw('count(*) as count')
            );

        if ($valueField) {
            $timeline->addSelect(DB::raw("sum({$valueField}) as total_value"));
        }

        return $timeline->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    /**
     * Get real-time dashboard (for websockets/polling)
     */
    public function getRealTimeMetrics(int $companyId): array
    {
        return [
            'active_users' => User::where('company_id', $companyId)
                ->where('last_activity_at', '>', now()->subMinutes(5))
                ->count(),
            'today_leads' => Lead::where('company_id', $companyId)
                ->whereDate('created_at', today())
                ->count(),
            'today_activities' => Activity::whereHas('user', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })->whereDate('created_at', today())->count(),
            'pending_tasks' => Task::whereHas('assignedTo', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })->where('status', 'pending')->count(),
            'overdue_tasks' => Task::whereHas('assignedTo', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            })->where('status', '!=', 'completed')
                ->where('due_date', '<', now())
                ->count(),
        ];
    }
}
