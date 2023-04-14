<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Group;
use App\Models\Location;
use App\Models\Training;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserTableSeeder::class,
            PermissionSeeder::class,
            ConfigTableSeeder::class,
        ]);
        Group::factory(3)->create();
        Location::factory(1)->create();
        User::factory(50)->create();
        Training::factory(200)->create();
    }
}
