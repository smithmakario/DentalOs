<?php

use App\Support\TenantDomain;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach (DB::table('domains')->orderBy('id')->get() as $domain) {
            $normalized = TenantDomain::normalize($domain->domain);

            if ($normalized === null || $normalized === $domain->domain) {
                continue;
            }

            DB::table('domains')
                ->where('id', $domain->id)
                ->update(['domain' => $normalized]);
        }
    }

    public function down(): void
    {
        //
    }
};
