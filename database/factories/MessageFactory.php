<?php

declare(strict_types=1);

namespace ZEDMagdy\FilamentChat\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use ZEDMagdy\FilamentChat\Models\Message;

class MessageFactory extends Factory
{
    protected $model = Message::class;

    public function definition(): array
    {
        return [
            'conversation_id' => null,
            'senderable_id' => null,
            'senderable_type' => null,
            'body' => $this->faker->sentence(),
            'metadata' => null,
        ];
    }

    public function system(): static
    {
        return $this->state([
            'senderable_id' => null,
            'senderable_type' => null,
        ]);
    }
}
