<?php
// app/Traits/HasTenant.php

namespace App\Traits;

use App\Scopes\TenantScope;
use App\Support\Tenancy;

/**
 * À placer sur les modèles racines possédés par un tenant (Site, QrCode).
 * - Applique le TenantScope (qui ne filtre qu'en mode multi).
 * - Assigne user_id à la création (utilisateur connecté en multi,
 *   propriétaire de l'instance en single).
 */
trait HasTenant
{
    public static function bootHasTenant(): void
    {
        static::addGlobalScope(new TenantScope());

        static::creating(function ($model) {
            if (empty($model->user_id)) {
                $ownerId = Tenancy::resourceOwnerId();
                if ($ownerId) {
                    $model->user_id = $ownerId;
                }
            }
        });
    }

    /** Requête sans le filtre tenant, quel que soit le mode. */
    public static function withoutTenant()
    {
        return static::withoutGlobalScope(TenantScope::class);
    }
}