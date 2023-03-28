<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        \App\Models\User::factory(50)->create();
        \App\Models\User::factory()->create([
             'firstname' => 'Sebastian',
             'lastname' => 'Binder',
             'nickname' => 'Sebi',
             'birthdate' => '1993-06-07',
             'email' => 'bindersebastian@online.de'
         ]);

        $this->call(PermissionSeeder::class);
    }
}
