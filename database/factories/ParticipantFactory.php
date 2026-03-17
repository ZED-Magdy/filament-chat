<?php

declare(strict_types=1);

namespace ZEDMagdy\FilamentChat\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use ZEDMagdy\FilamentChat\Models\Participant;

class ParticipantFactory extends Factory
{
    protected $model = Participant::class;

    public function definition(): array
    {
        return [
            'conversation_id' => null,
            'participantable_id' => null,
            'participantable_type' => null,
            'role' => 'member',
            'last_read_at' => null,
        ];
    }

    public function read(): static
    {
        return $this->state([
            'last_read_at' => now(),
        ]);
    }
}
