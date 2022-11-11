<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\SessionGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SessionGroup>
 */
class SessionGroupFactory extends Factory
{
    protected $model = SessionGroup::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'course_id' => Course::factory(),
            'max_ppl' => $this->faker->numberBetween(1, 200),
        ];
    }
}
