<?php

namespace Database\Seeders;

use App\Enums\Sales\OpportunityStatus;
use App\Models\Attachment;
use App\Models\Client;
use App\Models\Opportunity;
use App\Models\OpportunityMessage;
use App\Models\OpportunityNote;
use App\Models\OpportunityStatusLog;
use App\Models\User;
use Illuminate\Database\Seeder;

class SalesDemoSeeder extends Seeder
{
    public function run(): void
    {
        $distribution = [
            OpportunityStatus::New->value => 5,
            OpportunityStatus::Contacted->value => 5,
            OpportunityStatus::Qualified->value => 5,
            OpportunityStatus::ProposalSent->value => 5,
            OpportunityStatus::Negotiation->value => 5,
            OpportunityStatus::Won->value => 5,
            OpportunityStatus::Lost->value => 5,
            OpportunityStatus::Discarded->value => 5,
        ];

        $users = User::query()->pluck('id')->all();
        $clients = Client::query()->pluck('id')->all();

        foreach ($distribution as $status => $count) {
            for ($i = 0; $i < $count; $i++) {
                $opportunity = Opportunity::factory()->create([
                    'status' => $status,
                    'client_id' => in_array($status, ['proposal_sent', 'negotiation', 'won'], true) ? fake()->randomElement($clients) : fake()->optional(50)->randomElement($clients),
                    'responsible_user_id' => fake()->randomElement($users),
                    'estimated_ticket_amount' => in_array($status, ['qualified', 'proposal_sent', 'negotiation', 'won'], true)
                        ? fake()->numberBetween(200000, 3500000)
                        : null,
                ]);

                $this->createStatusLogs($opportunity, fake()->randomElement($users));

                OpportunityNote::factory()->count(fake()->numberBetween(2, 4))->create([
                    'opportunity_id' => $opportunity->id,
                    'by_user_id' => fake()->randomElement($users),
                ]);

                OpportunityMessage::factory()->count(fake()->numberBetween(4, 8))->create([
                    'opportunity_id' => $opportunity->id,
                ]);

                if (in_array($status, ['proposal_sent', 'negotiation', 'won'], true)) {
                    Attachment::factory()->count(fake()->numberBetween(1, 2))->create([
                        'attachable_type' => Opportunity::class,
                        'attachable_id' => $opportunity->id,
                        'uploaded_by' => fake()->randomElement($users),
                    ]);
                }
            }
        }

        $attachmentsMissing = 40 - Attachment::query()->where('attachable_type', Opportunity::class)->count();

        if ($attachmentsMissing > 0) {
            $eligibleOpportunityIds = Opportunity::query()
                ->whereIn('status', ['qualified', 'proposal_sent', 'negotiation', 'won'])
                ->pluck('id')
                ->all();

            for ($i = 0; $i < $attachmentsMissing; $i++) {
                Attachment::factory()->create([
                    'attachable_type' => Opportunity::class,
                    'attachable_id' => fake()->randomElement($eligibleOpportunityIds),
                    'uploaded_by' => fake()->randomElement($users),
                ]);
            }
        }
    }

    private function createStatusLogs(Opportunity $opportunity, ?int $userId): void
    {
        $sequence = match ($opportunity->status->value) {
            'new' => ['new'],
            'contacted' => ['new', 'contacted'],
            'qualified' => ['new', 'contacted', 'qualified'],
            'proposal_sent' => ['new', 'contacted', 'qualified', 'proposal_sent'],
            'negotiation' => ['new', 'contacted', 'qualified', 'proposal_sent', 'negotiation'],
            'won' => ['new', 'contacted', 'qualified', 'proposal_sent', 'negotiation', 'won'],
            'lost' => ['new', 'contacted', 'qualified', 'proposal_sent', 'negotiation', 'lost'],
            'discarded' => ['new', 'contacted', 'discarded'],
            default => ['new'],
        };

        $baseTime = now()->subDays(count($sequence) + fake()->numberBetween(10, 120));

        foreach ($sequence as $index => $status) {
            OpportunityStatusLog::create([
                'opportunity_id' => $opportunity->id,
                'status' => $status,
                'by_user_id' => $userId,
                'created_at' => $baseTime->copy()->addDays($index),
            ]);
        }
    }
}
