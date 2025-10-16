<?php

namespace Database\Seeders;

use App\Modules\User\Infra\Models\UserModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        UserModel::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }
}
