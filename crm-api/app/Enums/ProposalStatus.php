<?php

namespace App\Enums;

enum ProposalStatus: string
{
    case DRAFT = 'draft';
    case SENT = 'sent';
    case VIEWED = 'viewed';
    case ACCEPTED = 'accepted';
    case REJECTED = 'rejected';
    case EXPIRED = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Rascunho',
            self::SENT => 'Enviada',
            self::VIEWED => 'Visualizada',
            self::ACCEPTED => 'Aceita',
            self::REJECTED => 'Rejeitada',
            self::EXPIRED => 'Expirada',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::SENT => 'blue',
            self::VIEWED => 'indigo',
            self::ACCEPTED => 'green',
            self::REJECTED => 'red',
            self::EXPIRED => 'yellow',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::DRAFT => 'document',
            self::SENT => 'mail',
            self::VIEWED => 'eye',
            self::ACCEPTED => 'check-circle',
            self::REJECTED => 'x-circle',
            self::EXPIRED => 'clock',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function isAccepted(): bool
    {
        return $this === self::ACCEPTED;
    }

    public function isRejected(): bool
    {
        return $this === self::REJECTED;
    }

    public function isClosed(): bool
    {
        return in_array($this, [self::ACCEPTED, self::REJECTED, self::EXPIRED]);
    }

    public function canEdit(): bool
    {
        return $this === self::DRAFT;
    }
}
