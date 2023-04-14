<?php

namespace Database\Factories;

use App\Models\Group;
use App\Models\Training;
use App\Models\User;
use DateTime;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class TrainingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDateTraining = $this->faker->dateTimeThisYear('+2 months');
        return [
            'name' => $this->faker->word,
            'description' => $this->faker->sentence,
            'status' => $this->faker->boolean,
            'date_start' => $startDateTraining,
            'date_end' => $startDateTraining->modify('+2 hour'),
            'location_id' => 1,
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (Training $user) {
            // ...
        })->afterCreating(function (Training $training) {
            $training->users()->attach(User::inRandomOrder()->take(random_int(1, 15))->pluck('id'), ['role' => $this->faker->randomElement(['athlete', 'coach']),]);
            $training->groups()->attach(Group::inRandomOrder()->take(random_int(1, 3))->pluck('id'));
        });
    }

}
