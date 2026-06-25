<?php
// app/Services/PlanService.php

namespace App\Services;

use App\Support\Tenancy;
use Illuminate\Support\Facades\Auth;

/**
 * Vérifie quotas et statut d'abonnement.
 *
 * En mode SINGLE (entreprise dédiée), il n'y a pas d'abonnement SaaS :
 * tout est illimité et toujours actif. La logique de quotas ne s'applique
 * qu'en mode MULTI.
 */
class PlanService
{
    /**
     * L'utilisateur peut-il créer une ressource de ce type ?
     * $resource : 'sites' | 'buildings' | 'qrcodes' ...
     */
    public function canCreate(string $resource): bool
    {
        // Mode dédié → aucune limite.
        if (Tenancy::isSingle()) {
            return true;
        }

        $plan = $this->currentPlan();
        $limit = $plan['limits'][$resource] ?? null;

        // null ou -1 = illimité
        if ($limit === null || $limit < 0) {
            return true;
        }

        return $this->count($resource) < $limit;
    }

    /**
     * Une fonctionnalité est-elle disponible ?
     * $feature : 'analytics' | 'export_csv' ...
     */
    public function hasFeature(string $feature): bool
    {
        if (Tenancy::isSingle()) {
            return true; // tout est débloqué en mode entreprise
        }

        $plan = $this->currentPlan();
        return (bool) ($plan['features'][$feature] ?? false);
    }

    /** Le plan/abonnement est-il actif ? (toujours vrai en single) */
    public function isActive(): bool
    {
        if (Tenancy::isSingle()) {
            return true;
        }

        $user = Auth::user();
        if (! $user) {
            return false;
        }

        // Adapte selon ton schéma réel (colonne plan_expires_at, etc.)
        if (! empty($user->plan_expires_at)) {
            return now()->lessThanOrEqualTo($user->plan_expires_at);
        }

        return true;
    }

    /** % d'utilisation d'une ressource (0 en single, jamais de jauge). */
    public function usagePercent(string $resource): int
    {
        if (Tenancy::isSingle()) {
            return 0;
        }

        $plan = $this->currentPlan();
        $limit = $plan['limits'][$resource] ?? null;
        if ($limit === null || $limit <= 0) {
            return 0;
        }

        return (int) min(100, round($this->count($resource) / $limit * 100));
    }

    /** Clé du plan courant. */
    public function currentPlanKey(): string
    {
        if (Tenancy::isSingle()) {
            return config('tenancy.single_plan', 'enterprise');
        }

        return Auth::user()->plan ?? 'trial';
    }

    /** Définition complète du plan courant (depuis config/plans.php). */
    public function currentPlan(): array
    {
        $key = $this->currentPlanKey();
        return config("plans.$key", config('plans.enterprise', [
            'limits'   => [],
            'features' => [],
        ]));
    }

    /** Compte les ressources possédées (respecte le mode via le scope du modèle). */
    protected function count(string $resource): int
    {
        $map = [
            'sites'     => \App\Models\Site::class,
            'buildings' => \App\Models\Building::class,
            'qrcodes'   => \App\Models\QrCode::class,
        ];

        $model = $map[$resource] ?? null;
        if (! $model) {
            return 0;
        }

        // En multi, le TenantScope filtre déjà par user_id.
        // En single, on compte tout (pas de scope).
        return $model::query()->count();
    }
}