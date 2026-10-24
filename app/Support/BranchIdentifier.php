<?php

namespace App\Support;

use App\Models\Tenant;
use Illuminate\Support\Str;

class BranchIdentifier
{
    /**
     * Hostname (+ port when present) for path-based branch access.
     * Uses the current request when available so local ports stay correct.
     */
    public static function baseHost(): string
    {
        if (app()->bound('request') && filled(request()->getHost())) {
            return request()->getHttpHost();
        }

        $appUrl = (string) config('app.url');
        $host = parse_url($appUrl, PHP_URL_HOST);
        $port = parse_url($appUrl, PHP_URL_PORT);

        if (! is_string($host) || $host === '') {
            return 'localhost';
        }

        return $port ? strtolower($host).':'.$port : strtolower($host);
    }

    public static function suggestBranchName(string $clinicName): string
    {
        $clinicName = trim($clinicName);

        if ($clinicName === '') {
            return '';
        }

        return $clinicName.' - '.__('Main');
    }

    public static function slugFromName(string $name): string
    {
        $slug = Str::slug($name);

        if ($slug === '') {
            $slug = 'branch';
        }

        return Str::limit($slug, 63, '');
    }

    public static function uniqueSlug(string $name): string
    {
        $baseSlug = self::slugFromName($name);
        $slug = $baseSlug;
        $suffix = 1;

        while (
            Tenant::query()->where('slug', $slug)->exists()
            || Tenant::query()->whereKey($slug)->exists()
        ) {
            $suffixLabel = '-'.$suffix;
            $slug = Str::limit($baseSlug, 63 - strlen($suffixLabel), '').$suffixLabel;
            $suffix++;
        }

        return $slug;
    }

    /**
     * Fill missing branch identifiers from clinic/branch names.
     *
     * @param  array{name?: string|null, branch_name?: string|null, branch_slug?: string|null}  $input
     * @return array{branch_name?: string, branch_slug?: string}
     */
    public static function fillMissing(array $input): array
    {
        $clinicName = trim((string) ($input['name'] ?? ''));
        $branchName = trim((string) ($input['branch_name'] ?? ''));
        $slug = trim((string) ($input['branch_slug'] ?? ''));

        if ($branchName === '' && $clinicName !== '') {
            $branchName = self::suggestBranchName($clinicName);
        }

        if ($slug === '' && $branchName !== '') {
            $slug = self::uniqueSlug($branchName);
        } elseif ($slug !== '') {
            $slug = self::slugFromName($slug);
        }

        $filled = [];

        if ($branchName !== '') {
            $filled['branch_name'] = $branchName;
        }

        if ($slug !== '') {
            $filled['branch_slug'] = $slug;
        }

        return $filled;
    }
}
