<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        Role::query()->firstOrCreate([
            'name' => 'Admin',
        ]);
        Role::query()->firstOrCreate([
            'name' => 'User',
        ]);
    }
}
