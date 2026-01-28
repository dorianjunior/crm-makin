<?php

namespace App\Enums;

enum CompanyPlan: string
{
    case FREE = 'free';
    case BASIC = 'basic';
    case PREMIUM = 'premium';
    case ENTERPRISE = 'enterprise';

    public function label(): string
    {
        return match ($this) {
            self::FREE => 'Gratuito',
            self::BASIC => 'Básico',
            self::PREMIUM => 'Premium',
            self::ENTERPRISE => 'Enterprise',
        };
    }

    public function price(): float
    {
        return match ($this) {
            self::FREE => 0.00,
            self::BASIC => 29.90,
            self::PREMIUM => 79.90,
            self::ENTERPRISE => 199.90,
        };
    }

    public function maxUsers(): ?int
    {
        return match ($this) {
            self::FREE => 1,
            self::BASIC => 5,
            self::PREMIUM => 25,
            self::ENTERPRISE => null, // unlimited
        };
    }

    public function maxLeads(): ?int
    {
        return match ($this) {
            self::FREE => 50,
            self::BASIC => 500,
            self::PREMIUM => 5000,
            self::ENTERPRISE => null, // unlimited
        };
    }

    public function features(): array
    {
        return match ($this) {
            self::FREE => ['basic_crm', 'email_support'],
            self::BASIC => ['basic_crm', 'email_support', 'reports', 'api_access'],
            self::PREMIUM => ['basic_crm', 'email_support', 'reports', 'api_access', 'automation', 'integrations'],
            self::ENTERPRISE => ['basic_crm', 'email_support', 'reports', 'api_access', 'automation', 'integrations', 'custom_fields', 'priority_support', 'sla'],
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
