<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * Trait para aplicar automaticamente company_id em todos os models
 *
 * Uso:
 * class Lead extends Model {
 *     use HasCompanyScope;
 * }
 */
trait HasCompanyScope
{
    /**
     * Boot do trait
     */
    protected static function bootHasCompanyScope(): void
    {
        // Ao criar, adiciona company_id automaticamente
        static::creating(function ($model) {
            if (! $model->company_id && auth()->check()) {
                $model->company_id = auth()->user()->company_id;
            }
        });

        // Global scope para queries (só retorna da empresa do usuário)
        static::addGlobalScope('company', function (Builder $builder) {
            if (auth()->check() && auth()->user()->company_id) {
                $builder->where('company_id', auth()->user()->company_id);
            }
        });
    }

    /**
     * Remover scope temporariamente (apenas para admins da plataforma)
     */
    public function scopeAllCompanies(Builder $query): Builder
    {
        return $query->withoutGlobalScope('company');
    }
}
