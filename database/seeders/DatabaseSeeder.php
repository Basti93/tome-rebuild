<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Group;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Group::factory(10)->create();
        Location::factory(5)->create();
        $factory = User::factory(50);
        $factory->create();
        $factory->configure();
        $this->call([
            UserTableSeeder::class,
            PermissionSeeder::class,
            ConfigTableSeeder::class,
        ]);
    }
}
