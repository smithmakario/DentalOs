<?php

namespace App\Support;

class TenantDomain
{
    public static function normalize(?string $domain): ?string
    {
        if ($domain === null || $domain === '') {
            return $domain;
        }

        $domain = strtolower(trim($domain));
        $domain = preg_replace('#^https?://#i', '', $domain) ?? $domain;
        $domain = explode('/', $domain, 2)[0];
        $domain = preg_replace('#:\d+$#', '', $domain) ?? $domain;

        return $domain;
    }
}
