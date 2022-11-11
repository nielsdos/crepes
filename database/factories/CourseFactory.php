<?php

namespace Database\Factories;

use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Course>
 */
class CourseFactory extends Factory
{
    protected $model = Course::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $title = $this->faker->title;

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'description' => $this->faker->sentences(5, true),
            'last_date' => $this->faker->date,
            'notify_me' => $this->faker->boolean,
        ];
    }
}
