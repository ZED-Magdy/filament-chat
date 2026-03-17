<?php

declare(strict_types=1);

namespace ZEDMagdy\FilamentChat\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use ZEDMagdy\FilamentChat\Models\Conversation;

class ConversationFactory extends Factory
{
    protected $model = Conversation::class;

    public function definition(): array
    {
        return [
            'source' => 'staff',
            'type' => 'direct',
            'name' => null,
            'metadata' => null,
        ];
    }

    public function group(?string $name = null): static
    {
        return $this->state([
            'type' => 'group',
            'name' => $name ?? $this->faker->words(3, true),
        ]);
    }

    public function forSource(string $source): static
    {
        return $this->state(['source' => $source]);
    }
}
