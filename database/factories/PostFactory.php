<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Post;
use App\User;
use Faker\Generator as Faker;

$factory->define(Post::class, function (Faker $faker) {
    return [
        'title' => $faker->words(5, $asText = true),
        'content' => $faker->paragraphs(3, $asText = true),
        'publish_status' => $faker->boolean($chanceOfGettingTrue = 50),
        'user_id' => factory(User::class)
    ];
});
