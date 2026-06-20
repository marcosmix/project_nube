<?php

namespace Database\Factories;

use App\Enums\Sales\OpportunityMessageDirection;
use App\Enums\Sales\OpportunityMessageType;
use App\Models\Opportunity;
use App\Models\OpportunityMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

class OpportunityMessageFactory extends Factory
{
    protected $model = OpportunityMessage::class;

    public function definition(): array
    {
        return [
            'opportunity_id' => Opportunity::query()->inRandomOrder()->value('id'),
            'direction' => fake()->randomElement(OpportunityMessageDirection::cases())->value,
            'type' => fake()->randomElement(OpportunityMessageType::cases())->value,
            'content' => fake()->sentence(12),
            'external_message_id' => fake()->unique()->uuid(),
            'raw_payload' => ['channel' => 'seed', 'provider' => fake()->randomElement(['whatsapp', 'manual'])],
            'messaged_at' => fake()->dateTimeBetween('-9 months', 'now'),
            'status' => fake()->randomElement(['sent', 'delivered', 'read', 'received']),
        ];
    }
}
