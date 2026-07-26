<?php

namespace Database\Seeders;

use App\Models\PlatformAdmin;
use Illuminate\Database\Seeder;

class PlatformAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PlatformAdmin::firstOrCreate(
            ['email' => 'admin@nexuspos.com'],
            [
                'name' => 'Daniel Velásquez',
                'password' => 'password123', // cámbialo luego desde el sistema
            ]
        );
    }
}
