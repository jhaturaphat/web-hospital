<?php

namespace Database\Factories;

use App\Models\NewsCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class NewsPostFactory extends Factory
{
    public function definition(): array
    {
        return ['news_category_id' => NewsCategory::factory(), 'title' => fake()->sentence(), 'body' => fake()->paragraph(), 'is_published' => false];
    }
}