<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Image>
 */
class ImageFactory extends Factory
{
    private array $types = [
        'tvposter', 'tvbanner', 'tvthumb', 'hdtvlogo', 'clearlogo',
        'hdclearart', 'clearart', 'showbackground', 'characterart',
        'seasonposter', 'seasonbanner', 'seasonthumb',
    ];

    private array $languages = ['en', 'es', 'fr', 'de', 'ru', '00'];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => fake()->randomElement($this->types),
            'external_path' => fake()->url(),
            'internal_path' => null,
            'language' => fake()->randomElement($this->languages),
            'likes' => fake()->numberBetween(0, 20),
        ];
    }

    /**
     * Indicate that the image is stored locally.
     */
    public function stored(): static
    {
        return $this->state(fn (array $attributes) => [
            'internal_path' => 'images/'.fake()->uuid().'.jpg',
        ]);
    }

    /**
     * Indicate that the image is a show poster.
     */
    public function tvPoster(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'tvposter',
        ]);
    }

    /**
     * Indicate that the image is a season poster.
     */
    public function seasonPoster(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'seasonposter',
        ]);
    }
}
