<?php

namespace Database\Seeders;

use App\Modules\User\Domain\Enums\UserType;
use App\Modules\User\Infra\Models\UserModel;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        UserModel::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'type' => UserType::ORGANIZER,
        ]);
    }
}
