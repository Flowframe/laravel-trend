<?php

namespace Flowframe\Trend\Tests\Fixtures\Database\Factories;

use Flowframe\Trend\Tests\Fixtures\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    protected $model = Post::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(),
            'content' => fake()->sentence(),
            'summable_column' => fake()->numberBetween(1, 10),
        ];
    }
}
