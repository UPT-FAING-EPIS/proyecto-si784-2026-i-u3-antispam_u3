<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BlacklistWordFactory extends Factory
{
    protected $model = \App\Models\BlacklistWord::class;

    public function definition(): array
    {
        return [
            'word' => fake()->unique()->word(),
            'is_active' => true,
            'created_by' => null,
        ];
    }

    public function inactive(): self
    {
        return $this->state(['is_active' => false]);
    }
}
