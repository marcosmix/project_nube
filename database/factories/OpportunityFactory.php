<?php

namespace Database\Factories;

use App\Enums\Sales\OpportunitySource;
use App\Enums\Sales\OpportunityStatus;
use App\Models\Client;
use App\Models\Opportunity;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OpportunityFactory extends Factory
{
    protected $model = Opportunity::class;

    public function definition(): array
    {
        $contactName = fake()->name();
        $status = fake()->randomElement(OpportunityStatus::cases());

        return [
            'client_id' => Client::query()->inRandomOrder()->value('id'),
            'responsible_user_id' => User::query()->inRandomOrder()->value('id'),
            'name' => fake()->sentence(3),
            'status' => $status->value,
            'source' => fake()->randomElement(OpportunitySource::cases())->value,
            'whatsapp_contact_id' => null,
            'external_conversation_id' => fake()->optional()->uuid(),
            'first_contact_at' => fake()->optional()->dateTimeBetween('-10 months', 'now'),
            'first_customer_message_at' => fake()->optional()->dateTimeBetween('-10 months', '-1 day'),
            'last_customer_message_at' => fake()->optional()->dateTimeBetween('-9 months', 'now'),
            'customer_service_window_expires_at' => fake()->optional()->dateTimeBetween('now', '+3 days'),
            'contact_name' => $contactName,
            'contact_phone' => fake()->e164PhoneNumber(),
            'contact_email' => fake()->safeEmail(),
            'contact_handle' => fake()->optional()->userName(),
            'initial_message' => fake()->paragraph(),
            'estimated_ticket_amount' => in_array($status, [OpportunityStatus::Qualified, OpportunityStatus::ProposalSent, OpportunityStatus::Negotiation, OpportunityStatus::Won], true)
                ? fake()->numberBetween(150000, 3000000)
                : null,
        ];
    }
}
