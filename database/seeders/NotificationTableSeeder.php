<?php

namespace Database\Seeders;

use App\Models\NotificationSetting;
use Illuminate\Database\Seeder;

class NotificationTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        NotificationSetting::updateOrCreate(['id' => 1,], ['name' => 'trainingLocationUpdated',]);
        NotificationSetting::updateOrCreate(['id' => 2,], ['name' => 'trainingTimeUpdated',]);
        NotificationSetting::updateOrCreate(['id' => 3,], ['name' => 'trainingCanceled',]);
        NotificationSetting::updateOrCreate(['id' => 4,], ['name' => 'trainingCreated',]);
    }
}
