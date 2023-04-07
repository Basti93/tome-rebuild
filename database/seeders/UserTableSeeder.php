<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $usersDefault = [
            [
                'id' => 1,
                'firstname' => 'Admin',
                'lastname' => 'Testuser',
                'email' => env('MAIL_FROM_ADMIN'),
            ],
        ];

        if (env('APP_DEBUG')) {
            $usersDefault[] = [
                'id' => 2,
                'firstname' => 'Trainer',
                'lastname' => 'Testuser',
                'email' => env('MAIL_FROM_TEST_TRAINER'),
            ];

            $usersDefault[] = [
                'id' => 3,
                'firstname' => 'Benutzer',
                'lastname' => 'Testuser',
                'email' => env('MAIL_FROM_TEST_USER'),
            ];

            $usersDefault[] = [
                'id' => 4,
                'firstname' => 'NoPermission',
                'lastname' => 'Testuser',
                'email' => env('MAIL_FROM_NO_PERMISSION'),
            ];
        }

        foreach ($usersDefault as $user) {
            User::updateOrCreate(
                ['id' => $user['id']],
                [
                    'firstname' => $user['firstname'],
                    'lastname' => $user['lastname'],
                    'email' => $user['email'],
                    'birthdate' => now(),
                    'password' => Hash::make('password'),
                    'remember_token' => Str::random(10),
                ]
            );
        }
    }
}
