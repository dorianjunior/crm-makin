<?php

namespace App\Enums;

enum ContentType: string
{
    case PAGE = 'page';
    case POST = 'post';
    case PORTFOLIO = 'portfolio';
    case FAQ = 'faq';
    case TESTIMONIAL = 'testimonial';
    case TEAM_MEMBER = 'team_member';
    case FORM = 'form';
    case BANNER = 'banner';
    case MENU = 'menu';
    case SEO = 'seo';

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
            self::PAGE => 'Página',
            self::POST => 'Post',
            self::PORTFOLIO => 'Portfólio',
            self::FAQ => 'FAQ',
            self::TESTIMONIAL => 'Depoimento',
            self::TEAM_MEMBER => 'Membro da Equipe',
            self::FORM => 'Formulário',
            self::BANNER => 'Banner',
            self::MENU => 'Menu',
            self::SEO => 'SEO',
        };
    }

    /**
     * Get model class for the content type
     */
    public function modelClass(): string
    {
        return match ($this) {
            self::PAGE => \App\Models\CMS\Page::class,
            self::POST => \App\Models\CMS\Post::class,
            self::PORTFOLIO => \App\Models\CMS\Portfolio::class,
            self::FAQ => \App\Models\CMS\Faq::class,
            self::TESTIMONIAL => \App\Models\CMS\Testimonial::class,
            self::TEAM_MEMBER => \App\Models\CMS\TeamMember::class,
            self::FORM => \App\Models\CMS\Form::class,
            self::BANNER => \App\Models\CMS\Banner::class,
            self::MENU => \App\Models\CMS\Menu::class,
            self::SEO => \App\Models\CMS\Seo::class,
        };
    }
}
