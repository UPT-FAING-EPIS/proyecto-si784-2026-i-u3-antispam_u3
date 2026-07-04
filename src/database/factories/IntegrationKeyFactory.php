<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class IntegrationKeyFactory extends Factory
{
    protected $model = \App\Models\IntegrationKey::class;

    public function definition(): array
    {
        $plainKey = 'afk_' . Str::random(40);

        return [
            'channel' => 'wordpress',
            'label' => fake()->words(2, true),
            'key_hash' => hash('sha256', $plainKey),
            'key_prefix' => substr($plainKey, 0, 12),
            'is_active' => true,
            'created_by' => null,
        ];
    }

    public function revoked(): self
    {
        return $this->state(['is_active' => false]);
    }
}
