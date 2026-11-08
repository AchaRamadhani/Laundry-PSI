<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'ichabong19@gmail.com'],
            [
                'name' => "Admin G'Laundry",
                'username' => 'Andinurulrahmadani',
                'password' => 'admin.aryghava',
            ]
        );
    }
}   