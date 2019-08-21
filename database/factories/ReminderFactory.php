<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Reminder;
use Faker\Generator as Faker;

$factory->define(Reminder::class, function (Faker $faker) {
    return [
        'user_id' => function() {
            return factory('App\User')->create();
        },
        'title' => $faker->sentence,
        'due_at' => $faker->dateTimeBetween('1 hour', '48 hours'),
    ];
});
