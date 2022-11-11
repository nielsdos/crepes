<?php

namespace Database\Factories;

use App\Models\Session;
use App\Models\SessionDescription;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Session>
 */
class SessionFactory extends Factory
{
    protected $model = Session::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'location' => $this->faker->streetAddress,
            'start' => $this->faker->dateTime,
            'end' => $this->faker->dateTime,
            'session_description_id' => SessionDescription::factory(),
        ];
    }
}
