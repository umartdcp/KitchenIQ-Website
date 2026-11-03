<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(MenuSeeder::class);

        User::factory()->create([
            'name' => 'Demo Customer',
            'email' => 'demo@noshmark.test',
            'phone' => '+923001234567',
        ]);
    }
}
