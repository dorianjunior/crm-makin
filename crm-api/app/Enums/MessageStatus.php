<?php

namespace App\Enums;

enum MessageStatus: string
{
    case SENT = 'sent';
    case DELIVERED = 'delivered';
    case READ = 'read';
    case FAILED = 'failed';

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
            self::SENT => 'Enviado',
            self::DELIVERED => 'Entregue',
            self::READ => 'Lido',
            self::FAILED => 'Falhou',
        };
    }

    /**
     * Get icon for UI
     */
    public function icon(): string
    {
        return match ($this) {
            self::SENT => 'pi-check',
            self::DELIVERED => 'pi-check-circle',
            self::READ => 'pi-eye',
            self::FAILED => 'pi-times-circle',
        };
    }

    /**
     * Get color for UI
     */
    public function color(): string
    {
        return match ($this) {
            self::SENT => 'info',
            self::DELIVERED => 'success',
            self::READ => 'primary',
            self::FAILED => 'danger',
        };
    }
}
