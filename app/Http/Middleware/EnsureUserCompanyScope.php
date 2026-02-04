<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware para garantir que usuários só acessem dados da própria empresa
 *
 * Uso: Route::middleware(['auth', 'company.scope'])
 */
class EnsureUserCompanyScope
{
    public function handle(Request $request, Closure $next): Response
    {
        // Usuário autenticado
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        // Garantir que company_id está setado
        if (! $user->company_id) {
            abort(403, 'Usuário não está associado a nenhuma empresa');
        }

        // Adicionar company_id ao request para fácil acesso
        $request->merge([
            'company_id' => $user->company_id,
        ]);

        // Aplicar scope global em todas as queries do request
        // Isso garante que NUNCA vai acessar dados de outra empresa
        \App\Models\CRM\Lead::addGlobalScope('company', function ($query) use ($user) {
            $query->where('company_id', $user->company_id);
        });

        \App\Models\CMS\Page::addGlobalScope('company', function ($query) use ($user) {
            $query->where('company_id', $user->company_id);
        });

        \App\Models\CMS\Post::addGlobalScope('company', function ($query) use ($user) {
            $query->where('company_id', $user->company_id);
        });

        // Adicione outros models aqui...

        return $next($request);
    }
}
