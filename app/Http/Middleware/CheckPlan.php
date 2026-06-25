<?php
// app/Http/Middleware/CheckPlan.php

namespace App\Http\Middleware;

use App\Support\Tenancy;
use App\Services\PlanService;
use Closure;
use Illuminate\Http\Request;

/**
 * Vérifie le statut du plan SaaS et affiche les bannières d'expiration.
 *
 * En mode SINGLE (entreprise dédiée) : ce middleware ne fait RIEN,
 * il laisse simplement passer la requête.
 */
class CheckPlan
{
    public function __construct(protected PlanService $plans) {}

    public function handle(Request $request, Closure $next)
    {
        // Mode dédié → pas de notion d'abonnement, on passe.
        if (Tenancy::isSingle()) {
            return $next($request);
        }

        // Mode SaaS → logique d'abonnement habituelle.
        if (auth()->check() && ! $this->plans->isActive()) {
            // Exemple : rediriger vers la page de renouvellement.
            // Adapte selon ton implémentation existante.
            if (! $request->routeIs('plans.*', 'logout')) {
                return redirect()
                    ->route('plans.index')
                    ->with('error', 'Votre abonnement a expiré. Veuillez le renouveler.');
            }
        }

        // Partage l'info de bannière aux vues (si tu l'utilises déjà).
        view()->share('planActive', $this->plans->isActive());

        return $next($request);
    }
}