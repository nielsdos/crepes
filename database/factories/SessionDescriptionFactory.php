<?php

namespace Database\Factories;

use App\Models\SessionDescription;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SessionDescription>
 */
class SessionDescriptionFactory extends Factory
{
    protected $model = SessionDescription::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'description' => $this->faker->sentences(3, true),
        ];
    }
}
