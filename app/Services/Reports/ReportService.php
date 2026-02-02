<?php

namespace App\Services\Reports;

use App\Models\Report;
use App\Models\Lead;
use App\Models\Proposal;
use App\Models\Activity;
use App\Models\Task;
use App\Models\User;
use App\Models\PipelineStage;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;

class ReportService
{
    /**
     * Execute a report and return results
     */
    public function execute(Report $report, array $additionalFilters = []): array
    {
        $report->incrementExecutionCount();

        $filters = array_merge($report->filters ?? [], $additionalFilters);

        $query = $this->buildQuery($report->type, $filters);

        // Apply sorting
        if ($report->sorting) {
            foreach ($report->sorting as $sort) {
                $query->orderBy($sort['field'], $sort['direction'] ?? 'asc');
            }
        }

        // Get results
        $results = $query->get();

        // Apply grouping if needed
        if ($report->grouping) {
            $results = $this->applyGrouping($results, $report->grouping);
        }

        // Select only specified columns
        if ($report->columns) {
            $results = $this->selectColumns($results, $report->columns);
        }

        return [
            'data' => $results,
            'summary' => $this->generateSummary($results, $report->type),
            'chart_data' => $report->hasChartConfig() ? $this->generateChartData($results, $report->chart_config) : null,
        ];
    }

    /**
     * Build query based on report type
     */
    private function buildQuery(string $type, array $filters): Builder
    {
        switch ($type) {
            case 'leads':
                return $this->buildLeadsQuery($filters);

            case 'sales':
                return $this->buildSalesQuery($filters);

            case 'activities':
                return $this->buildActivitiesQuery($filters);

            case 'tasks':
                return $this->buildTasksQuery($filters);

            case 'proposals':
                return $this->buildProposalsQuery($filters);

            case 'pipeline':
                return $this->buildPipelineQuery($filters);

            case 'users':
                return $this->buildUsersQuery($filters);

            default:
                throw new \Exception("Report type '{$type}' not supported");
        }
    }

    /**
     * Build leads query
     */
    private function buildLeadsQuery(array $filters): Builder
    {
        $query = Lead::query()->with(['user', 'company', 'leadSource']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['source_id'])) {
            $query->where('lead_source_id', $filters['source_id']);
        }

        if (!empty($filters['assigned_to_user_id'])) {
            $query->where('assigned_to_user_id', $filters['assigned_to_user_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (!empty($filters['value_min'])) {
            $query->where('value', '>=', $filters['value_min']);
        }

        if (!empty($filters['value_max'])) {
            $query->where('value', '<=', $filters['value_max']);
        }

        return $query;
    }

    /**
     * Build sales query
     */
    private function buildSalesQuery(array $filters): Builder
    {
        $query = Proposal::query()
            ->with(['lead', 'user', 'company'])
            ->where('status', 'accepted');

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('accepted_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('accepted_at', '<=', $filters['date_to']);
        }

        if (!empty($filters['value_min'])) {
            $query->where('total', '>=', $filters['value_min']);
        }

        if (!empty($filters['value_max'])) {
            $query->where('total', '<=', $filters['value_max']);
        }

        return $query;
    }

    /**
     * Build activities query
     */
    private function buildActivitiesQuery(array $filters): Builder
    {
        $query = Activity::query()->with(['lead', 'user']);

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['lead_id'])) {
            $query->where('lead_id', $filters['lead_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query;
    }

    /**
     * Build tasks query
     */
    private function buildTasksQuery(array $filters): Builder
    {
        $query = Task::query()->with(['assignedTo', 'createdBy', 'lead']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (!empty($filters['assigned_to_user_id'])) {
            $query->where('assigned_to_user_id', $filters['assigned_to_user_id']);
        }

        if (!empty($filters['due_date_from'])) {
            $query->whereDate('due_date', '>=', $filters['due_date_from']);
        }

        if (!empty($filters['due_date_to'])) {
            $query->whereDate('due_date', '<=', $filters['due_date_to']);
        }

        return $query;
    }

    /**
     * Build proposals query
     */
    private function buildProposalsQuery(array $filters): Builder
    {
        $query = Proposal::query()->with(['lead', 'user', 'company']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query;
    }

    /**
     * Build pipeline query
     */
    private function buildPipelineQuery(array $filters): Builder
    {
        $query = PipelineStage::query()
            ->with(['pipeline'])
            ->withCount('leads')
            ->withSum('leads', 'value');

        if (!empty($filters['pipeline_id'])) {
            $query->where('pipeline_id', $filters['pipeline_id']);
        }

        return $query;
    }

    /**
     * Build users query
     */
    private function buildUsersQuery(array $filters): Builder
    {
        $query = User::query()
            ->with(['role', 'company'])
            ->withCount(['leads', 'activities']);

        if (!empty($filters['role_id'])) {
            $query->where('role_id', $filters['role_id']);
        }

        if (!empty($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query;
    }

    /**
     * Apply grouping to results
     */
    private function applyGrouping($results, array $grouping): array
    {
        $groupField = $grouping['field'] ?? null;

        if (!$groupField) {
            return $results->toArray();
        }

        return $results->groupBy($groupField)->map(function ($group, $key) use ($grouping) {
            $aggregations = [];

            if (!empty($grouping['aggregations'])) {
                foreach ($grouping['aggregations'] as $agg) {
                    $field = $agg['field'];
                    $operation = $agg['operation']; // sum, avg, count, min, max

                    switch ($operation) {
                        case 'sum':
                            $aggregations[$field . '_sum'] = $group->sum($field);
                            break;
                        case 'avg':
                            $aggregations[$field . '_avg'] = $group->avg($field);
                            break;
                        case 'count':
                            $aggregations[$field . '_count'] = $group->count();
                            break;
                        case 'min':
                            $aggregations[$field . '_min'] = $group->min($field);
                            break;
                        case 'max':
                            $aggregations[$field . '_max'] = $group->max($field);
                            break;
                    }
                }
            }

            return [
                'group' => $key,
                'count' => $group->count(),
                'items' => $group->toArray(),
                'aggregations' => $aggregations,
            ];
        })->values()->toArray();
    }

    /**
     * Select only specified columns
     */
    private function selectColumns($results, array $columns)
    {
        return $results->map(function ($item) use ($columns) {
            $filtered = [];
            foreach ($columns as $column) {
                if (is_array($item)) {
                    $filtered[$column] = $item[$column] ?? null;
                } else {
                    $filtered[$column] = $item->$column ?? null;
                }
            }
            return $filtered;
        });
    }

    /**
     * Generate summary statistics
     */
    private function generateSummary($results, string $type): array
    {
        $summary = [
            'total_records' => $results->count(),
        ];

        switch ($type) {
            case 'leads':
                $summary['total_value'] = $results->sum('value');
                $summary['avg_value'] = $results->avg('value');
                break;

            case 'sales':
                $summary['total_revenue'] = $results->sum('total');
                $summary['avg_ticket'] = $results->avg('total');
                break;

            case 'tasks':
                $summary['completed'] = $results->where('status', 'completed')->count();
                $summary['pending'] = $results->where('status', 'pending')->count();
                $summary['overdue'] = $results->where('status', 'overdue')->count();
                break;

            case 'proposals':
                $summary['accepted'] = $results->where('status', 'accepted')->count();
                $summary['rejected'] = $results->where('status', 'rejected')->count();
                $summary['pending'] = $results->where('status', 'pending')->count();
                $summary['total_value'] = $results->sum('total');
                break;
        }

        return $summary;
    }

    /**
     * Generate chart data
     */
    private function generateChartData($results, array $chartConfig): array
    {
        $chartType = $chartConfig['type'] ?? 'bar';
        $xAxis = $chartConfig['x_axis'] ?? 'created_at';
        $yAxis = $chartConfig['y_axis'] ?? 'count';

        $data = [];

        if ($yAxis === 'count') {
            // Group by x_axis and count
            $grouped = $results->groupBy($xAxis);
            foreach ($grouped as $key => $group) {
                $data[] = [
                    'label' => $key,
                    'value' => $group->count(),
                ];
            }
        } else {
            // Group by x_axis and sum y_axis
            $grouped = $results->groupBy($xAxis);
            foreach ($grouped as $key => $group) {
                $data[] = [
                    'label' => $key,
                    'value' => $group->sum($yAxis),
                ];
            }
        }

        return [
            'type' => $chartType,
            'data' => $data,
            'config' => $chartConfig,
        ];
    }

    /**
     * Get available filters for a report type
     */
    public function getAvailableFilters(string $type): array
    {
        $filters = [
            'leads' => [
                ['name' => 'status', 'type' => 'select', 'options' => ['new', 'contacted', 'qualified', 'converted', 'lost']],
                ['name' => 'source_id', 'type' => 'select', 'label' => 'Fonte'],
                ['name' => 'assigned_to_user_id', 'type' => 'select', 'label' => 'Responsável'],
                ['name' => 'date_from', 'type' => 'date', 'label' => 'Data Inicial'],
                ['name' => 'date_to', 'type' => 'date', 'label' => 'Data Final'],
                ['name' => 'value_min', 'type' => 'number', 'label' => 'Valor Mínimo'],
                ['name' => 'value_max', 'type' => 'number', 'label' => 'Valor Máximo'],
            ],
            'sales' => [
                ['name' => 'user_id', 'type' => 'select', 'label' => 'Vendedor'],
                ['name' => 'date_from', 'type' => 'date', 'label' => 'Data Inicial'],
                ['name' => 'date_to', 'type' => 'date', 'label' => 'Data Final'],
                ['name' => 'value_min', 'type' => 'number', 'label' => 'Valor Mínimo'],
                ['name' => 'value_max', 'type' => 'number', 'label' => 'Valor Máximo'],
            ],
            'activities' => [
                ['name' => 'type', 'type' => 'select', 'options' => ['call', 'email', 'meeting', 'note']],
                ['name' => 'user_id', 'type' => 'select', 'label' => 'Usuário'],
                ['name' => 'lead_id', 'type' => 'select', 'label' => 'Lead'],
                ['name' => 'date_from', 'type' => 'date', 'label' => 'Data Inicial'],
                ['name' => 'date_to', 'type' => 'date', 'label' => 'Data Final'],
            ],
            'tasks' => [
                ['name' => 'status', 'type' => 'select', 'options' => ['pending', 'in_progress', 'completed', 'overdue']],
                ['name' => 'priority', 'type' => 'select', 'options' => ['low', 'normal', 'high', 'urgent']],
                ['name' => 'assigned_to_user_id', 'type' => 'select', 'label' => 'Responsável'],
                ['name' => 'due_date_from', 'type' => 'date', 'label' => 'Vencimento De'],
                ['name' => 'due_date_to', 'type' => 'date', 'label' => 'Vencimento Até'],
            ],
            'proposals' => [
                ['name' => 'status', 'type' => 'select', 'options' => ['pending', 'sent', 'accepted', 'rejected']],
                ['name' => 'user_id', 'type' => 'select', 'label' => 'Usuário'],
                ['name' => 'date_from', 'type' => 'date', 'label' => 'Data Inicial'],
                ['name' => 'date_to', 'type' => 'date', 'label' => 'Data Final'],
            ],
            'pipeline' => [
                ['name' => 'pipeline_id', 'type' => 'select', 'label' => 'Pipeline'],
            ],
            'users' => [
                ['name' => 'role_id', 'type' => 'select', 'label' => 'Papel'],
                ['name' => 'is_active', 'type' => 'boolean', 'label' => 'Ativo'],
            ],
        ];

        return $filters[$type] ?? [];
    }
}
