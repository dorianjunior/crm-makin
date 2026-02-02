<?php

namespace App\Enums;

enum LeadStatus: string
{
    case NEW = 'new';
    case CONTACTED = 'contacted';
    case QUALIFIED = 'qualified';
    case PROPOSAL = 'proposal';
    case NEGOTIATION = 'negotiation';
    case WON = 'won';
    case LOST = 'lost';

    public function label(): string
    {
        return match ($this) {
            self::NEW => 'Novo',
            self::CONTACTED => 'Contatado',
            self::QUALIFIED => 'Qualificado',
            self::PROPOSAL => 'Proposta',
            self::NEGOTIATION => 'Negociação',
            self::WON => 'Ganho',
            self::LOST => 'Perdido',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::NEW => 'blue',
            self::CONTACTED => 'indigo',
            self::QUALIFIED => 'purple',
            self::PROPOSAL => 'yellow',
            self::NEGOTIATION => 'orange',
            self::WON => 'green',
            self::LOST => 'red',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::NEW => 'plus-circle',
            self::CONTACTED => 'phone',
            self::QUALIFIED => 'check-circle',
            self::PROPOSAL => 'document-text',
            self::NEGOTIATION => 'chat-alt-2',
            self::WON => 'check-badge',
            self::LOST => 'x-circle',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function isWon(): bool
    {
        return $this === self::WON;
    }

    public function isLost(): bool
    {
        return $this === self::LOST;
    }

    public function isClosed(): bool
    {
        return $this === self::WON || $this === self::LOST;
    }
}
