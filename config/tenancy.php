<?php
// config/tenancy.php

return [
    // "multi" = SaaS multi-tenant ; "single" = instance dédiée entreprise.
    'mode' => env('TENANCY_MODE', 'multi'),

    // Mode single : propriétaire pivot (sinon 1er utilisateur de la base).
    'owner_id' => env('TENANCY_OWNER_ID', null),

    // Mode single : nom de l'entreprise (affichage).
    'organization' => env('TENANCY_ORG_NAME', null),

    // Plan forcé en mode single (PlanService laisse tout passer).
    'single_plan' => env('TENANCY_SINGLE_PLAN', 'enterprise'),
];