<?php
// app/Support/Tenancy.php

namespace App\Support;

use App\Models\User;

class Tenancy
{
    public static function mode(): string
    {
        return config('tenancy.mode', 'multi') === 'single' ? 'single' : 'multi';
    }

    public static function isSingle(): bool { return self::mode() === 'single'; }
    public static function isMulti(): bool  { return self::mode() === 'multi'; }

    /** Propriétaire de l'instance en mode single (config, sinon 1er user). */
    public static function ownerId(): ?int
    {
        if (! self::isSingle()) {
            return auth()->id();
        }

        $configured = config('tenancy.owner_id');
        if ($configured) {
            return (int) $configured;
        }

        static $cached = null;
        if ($cached !== null) {
            return $cached;
        }
        return $cached = User::query()->orderBy('id')->value('id');
    }

    /** user_id à assigner à une ressource créée maintenant. */
    public static function resourceOwnerId(): ?int
    {
        return self::isSingle() ? self::ownerId() : auth()->id();
    }

    public static function organization(): ?string
    {
        return self::isSingle() ? config('tenancy.organization') : null;
    }
}