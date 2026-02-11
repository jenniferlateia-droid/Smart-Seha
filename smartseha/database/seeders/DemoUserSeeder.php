<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@smartseha.com'],
            [
                'name' => 'Smart Seha Admin',
                'password' => Hash::make('password123'),
                'is_admin' => true,
            ]
        );
    }
}
