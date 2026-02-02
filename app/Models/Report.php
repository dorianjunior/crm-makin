<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'user_id',
        'name',
        'slug',
        'type',
        'description',
        'filters',
        'columns',
        'grouping',
        'sorting',
        'chart_config',
        'is_public',
        'is_favorite',
        'last_executed_at',
        'execution_count',
    ];

    protected $casts = [
        'filters' => 'array',
        'columns' => 'array',
        'grouping' => 'array',
        'sorting' => 'array',
        'chart_config' => 'array',
        'is_public' => 'boolean',
        'is_favorite' => 'boolean',
        'last_executed_at' => 'datetime',
        'execution_count' => 'integer',
    ];

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($report) {
            if (empty($report->slug)) {
                $report->slug = Str::slug($report->name) . '-' . Str::random(6);
            }
        });
    }

    /**
     * Relationships
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(ReportSchedule::class);
    }

    public function exports(): HasMany
    {
        return $this->hasMany(ReportExport::class);
    }

    /**
     * Scopes
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeFavorite($query)
    {
        return $query->where('is_favorite', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('user_id', $userId)
              ->orWhere('is_public', true);
        });
    }

    public function scopeSearch($query, ?string $search)
    {
        if (!$search) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    /**
     * Methods
     */
    public function incrementExecutionCount(): void
    {
        $this->increment('execution_count');
        $this->update(['last_executed_at' => now()]);
    }

    public function toggleFavorite(): bool
    {
        $this->is_favorite = !$this->is_favorite;
        $this->save();

        return $this->is_favorite;
    }

    public function duplicate(string $name = null): self
    {
        $newReport = $this->replicate(['slug']);
        $newReport->name = $name ?? $this->name . ' (Copy)';
        $newReport->slug = Str::slug($newReport->name) . '-' . Str::random(6);
        $newReport->is_favorite = false;
        $newReport->execution_count = 0;
        $newReport->last_executed_at = null;
        $newReport->save();

        return $newReport;
    }

    public function hasFilters(): bool
    {
        return !empty($this->filters);
    }

    public function hasChartConfig(): bool
    {
        return !empty($this->chart_config);
    }

    public function getChartType(): ?string
    {
        return $this->chart_config['type'] ?? null;
    }

    public function isScheduled(): bool
    {
        return $this->schedules()->where('is_active', true)->exists();
    }

    public function getRecentExports(int $limit = 5)
    {
        return $this->exports()
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Get available report types
     */
    public static function getAvailableTypes(): array
    {
        return [
            'leads' => 'Relatório de Leads',
            'sales' => 'Relatório de Vendas',
            'activities' => 'Relatório de Atividades',
            'tasks' => 'Relatório de Tarefas',
            'proposals' => 'Relatório de Propostas',
            'pipeline' => 'Relatório de Pipeline',
            'users' => 'Relatório de Usuários',
            'custom' => 'Relatório Personalizado',
        ];
    }

    /**
     * Get available columns for each type
     */
    public static function getAvailableColumns(string $type): array
    {
        $columns = [
            'leads' => [
                'id' => 'ID',
                'name' => 'Nome',
                'email' => 'Email',
                'phone' => 'Telefone',
                'company' => 'Empresa',
                'status' => 'Status',
                'source' => 'Fonte',
                'value' => 'Valor',
                'created_at' => 'Data de Criação',
                'assigned_to' => 'Responsável',
            ],
            'sales' => [
                'proposal_number' => 'Número da Proposta',
                'lead_name' => 'Cliente',
                'total' => 'Valor Total',
                'status' => 'Status',
                'created_at' => 'Data de Criação',
                'accepted_at' => 'Data de Aceite',
                'user' => 'Vendedor',
            ],
            'activities' => [
                'id' => 'ID',
                'type' => 'Tipo',
                'description' => 'Descrição',
                'lead_name' => 'Lead',
                'user' => 'Usuário',
                'created_at' => 'Data',
            ],
            'tasks' => [
                'id' => 'ID',
                'title' => 'Título',
                'status' => 'Status',
                'priority' => 'Prioridade',
                'due_date' => 'Vencimento',
                'assigned_to' => 'Responsável',
                'created_by' => 'Criado Por',
            ],
            'proposals' => [
                'number' => 'Número',
                'lead_name' => 'Cliente',
                'total' => 'Valor',
                'status' => 'Status',
                'valid_until' => 'Válido até',
                'created_at' => 'Data de Criação',
            ],
            'pipeline' => [
                'stage_name' => 'Etapa',
                'leads_count' => 'Qtd Leads',
                'total_value' => 'Valor Total',
                'conversion_rate' => 'Taxa de Conversão',
            ],
            'users' => [
                'name' => 'Nome',
                'email' => 'Email',
                'role' => 'Papel',
                'leads_count' => 'Qtd Leads',
                'activities_count' => 'Qtd Atividades',
                'created_at' => 'Data de Cadastro',
            ],
        ];

        return $columns[$type] ?? [];
    }
}
