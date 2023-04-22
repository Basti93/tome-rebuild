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

        $trainer = User::whereEmail(env('MAIL_FROM_TEST_TRAINER'))->first();
        $user = User::whereEmail(env('MAIL_FROM_TEST_USER'))->first();
        $trainer->groups()->attach(1, ['role' => 'coach']);
        $trainer->groups()->attach(1, ['role' => 'athlete']);
        $user->groups()->attach(1, ['role' => 'athlete']);

        $trainingsWithGroup = Training::whereHas('groups', function ($query) {
            $query->whereGroupId(1);
        });
        $i = 0;
        foreach ($trainingsWithGroup->get() as $training) {
            $training->coaches()->attach($trainer->id);
            $training->athletesAttending()->attach($user->id);
            $training->athletesAttending()->attach($trainer->id);
            if ($i == 20) {
                break;
            }
            $i++;
        }
    }
}
