<?php

namespace Database\Seeders;

use App\Models\ClinicService;
use Illuminate\Database\Seeder;

class ClinicServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            [
                'code' => 'PREV-001',
                'name' => 'Routine Checkup',
                'description' => 'Comprehensive oral examination, digital X-rays, and professional consultation.',
                'category' => 'Preventive',
                'price' => 80000,
                'duration_minutes' => 45,
                'icon' => 'medical_services',
                'is_recommended' => false,
            ],
            [
                'code' => 'PREV-002',
                'name' => 'Dental Cleaning',
                'description' => 'Professional scale and polish to remove plaque and tartar for a healthier smile.',
                'category' => 'Preventive',
                'price' => 120000,
                'duration_minutes' => 60,
                'icon' => 'clean_hands',
                'is_recommended' => true,
            ],
            [
                'code' => 'COSM-001',
                'name' => 'Teeth Whitening',
                'description' => 'Advanced laser whitening treatment for a brighter, more confident smile.',
                'category' => 'Cosmetic',
                'price' => 350000,
                'duration_minutes' => 90,
                'icon' => 'auto_awesome',
                'is_recommended' => false,
            ],
            [
                'code' => 'EMER-001',
                'name' => 'Emergency Care',
                'description' => 'Urgent treatment for toothaches, broken teeth, or acute dental trauma.',
                'category' => 'Emergency',
                'price' => 150000,
                'duration_minutes' => 30,
                'icon' => 'emergency',
                'is_recommended' => false,
            ],
        ];

        foreach ($services as $service) {
            ClinicService::query()->updateOrCreate(
                ['code' => $service['code']],
                array_merge($service, ['is_active' => true]),
            );
        }
    }
}
