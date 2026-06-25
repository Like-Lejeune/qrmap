<?php
// app/Scopes/TenantScope.php

namespace App\Scopes;

use App\Support\Tenancy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Isolation par tenant (user_id).
 *  - mode multi  : WHERE user_id = auth()->id() (si un user est connecté)
 *  - mode single : aucun filtre (l'entreprise possède toute la base)
 */
class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (Tenancy::isSingle()) {
            return; // mode dédié : pas de cloisonnement
        }

        if (auth()->check()) {
            $builder->where($model->getTable() . '.user_id', auth()->id());
        }
    }
}