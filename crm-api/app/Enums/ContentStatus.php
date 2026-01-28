<?php

namespace App\Enums;

enum ContentStatus: string
{
    case DRAFT = 'draft';
    case PENDING = 'pending';
    case PUBLISHED = 'published';

    /**
     * Get all possible values
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get label for display
     */
    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Rascunho',
            self::PENDING => 'Pendente',
            self::PUBLISHED => 'Publicado',
        };
    }

    /**
     * Get color for UI
     */
    public function color(): string
    {
        return match ($this) {
            self::DRAFT => 'warning',
            self::PENDING => 'info',
            self::PUBLISHED => 'success',
        };
    }
}
