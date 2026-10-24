<?php

namespace Database\Seeders;

use App\Enums\PlatformRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = (string) env('ADMIN_EMAIL', 'admin@dentalos.test');
        $password = (string) env('ADMIN_PASSWORD', 'password');
        $name = (string) env('ADMIN_NAME', 'DentalOS Super Admin');

        User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
                'platform_role' => PlatformRole::SuperAdmin,
                'email_verified_at' => now(),
            ],
        );

        $this->command?->info("Super admin ready: {$email}");
    }
}
