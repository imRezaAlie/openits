<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@openits.local'],
            [
                'name' => 'OpenITS Admin',
                'password' => 'password',
                'is_admin' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => 'password',
                'is_admin' => false,
            ]
        );
    }
}
