<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ReminderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => function() {
                return User::factory()->create();
            },
            'title' => $this->faker->sentence,
            'due_at' => $this->faker->dateTimeBetween('1 hour', '48 hours'),
        ];
    }
}
