<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('payments')->where('payment_method', 'card')->update(['payment_method' => 'pos']);
        DB::table('payments')->where('payment_method', 'bank_transfer')->update(['payment_method' => 'transfer']);
        DB::table('payments')->where('payment_method', 'insurance')->update(['payment_method' => 'hmo']);
        DB::table('payments')->where('payment_method', 'other')->update(['payment_method' => 'credit']);
    }

    public function down(): void
    {
        DB::table('payments')->where('payment_method', 'pos')->update(['payment_method' => 'card']);
        DB::table('payments')->where('payment_method', 'transfer')->update(['payment_method' => 'bank_transfer']);
        DB::table('payments')->where('payment_method', 'hmo')->update(['payment_method' => 'insurance']);
        DB::table('payments')->where('payment_method', 'credit')->update(['payment_method' => 'other']);
    }
};
