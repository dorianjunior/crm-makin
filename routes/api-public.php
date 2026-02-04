<?php

use App\Http\Controllers\API\Public\ContentController;
use Illuminate\Support\Facades\Route;

/**
 * Rotas públicas para sites dos clientes
 *
 * Exemplo de uso:
 *
 * // No site do cliente (WordPress, HTML, etc):
 * fetch('https://sua-plataforma.com/api/public/pages?site_key=abc123')
 *   .then(res => res.json())
 *   .then(pages => console.log(pages))
 *
 * // Criar lead do formulário de contato:
 * fetch('https://sua-plataforma.com/api/public/leads', {
 *   method: 'POST',
 *   headers: {
 *     'Content-Type': 'application/json',
 *     'X-Site-Key': 'abc123'
 *   },
 *   body: JSON.stringify({
 *     name: 'João Silva',
 *     email: 'joao@email.com',
 *     phone: '11999999999',
 *     message: 'Gostaria de saber mais sobre...'
 *   })
 * })
 */

// Grupo de rotas públicas (sem autenticação)
Route::prefix('public')->group(function () {
    // Content API (CMS)
    Route::get('/pages', [ContentController::class, 'pages']);
    Route::get('/pages/{slug}', [ContentController::class, 'page']);
    Route::get('/posts', [ContentController::class, 'posts']);
    Route::get('/posts/{slug}', [ContentController::class, 'post']);
    Route::get('/portfolio', [ContentController::class, 'portfolio']);
    Route::get('/team', [ContentController::class, 'team']);
    Route::get('/testimonials', [ContentController::class, 'testimonials']);

    // Lead Capture
    Route::post('/leads', [ContentController::class, 'createLead']);

    // Contact Form
    Route::post('/contact', [ContentController::class, 'contact']);
});

// Rate limiting para API pública
Route::middleware(['throttle:60,1'])->group(function () {
    // Rotas com rate limit mais restritivo
});
