<?php

namespace Tests\Feature\Cobros;

use App\Enums\Cobros\ScheduledChargeFrequency;
use App\Enums\Cobros\ScheduledChargeStatus;
use App\Livewire\Cobros\ScheduledCharges\Create as CreateScheduledCharge;
use App\Models\Client;
use App\Models\ScheduledCharge;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ScheduledChargeCreateTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_scheduled_charge_for_any_client(): void
    {
        $user = User::factory()->create();
        $client = Client::factory()->create(['organization_name' => 'Cliente Programado']);

        $this->actingAs($user);

        Livewire::test(CreateScheduledCharge::class)
            ->call('selectClient', $client->id)
            ->set('reference_name', 'Hosting mensual')
            ->set('amount', '150000')
            ->set('charge_date', '2026-07-10')
            ->set('frequency', ScheduledChargeFrequency::Monthly->value)
            ->set('detail', 'Servicio recurrente de hosting')
            ->call('save');

        $this->assertDatabaseHas('scheduled_charges', [
            'client_id' => $client->id,
            'created_by' => $user->id,
            'reference_name' => 'Hosting mensual',
            'amount' => 150000,
            'charge_date' => '2026-07-10 00:00:00',
            'frequency' => ScheduledChargeFrequency::Monthly->value,
            'status' => ScheduledChargeStatus::Active->value,
        ]);
    }

    public function test_it_creates_a_scheduled_charge_with_optional_attachment(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $client = Client::factory()->create();

        $this->actingAs($user);

        Livewire::test(CreateScheduledCharge::class)
            ->call('selectClient', $client->id)
            ->set('reference_name', 'Abono anual')
            ->set('amount', '300000')
            ->set('charge_date', '2026-08-01')
            ->set('frequency', ScheduledChargeFrequency::Yearly->value)
            ->set('attachmentUpload', UploadedFile::fake()->create('contrato.pdf', 128, 'application/pdf'))
            ->call('save');

        $scheduledCharge = ScheduledCharge::query()->firstOrFail();

        $this->assertDatabaseHas('attachments', [
            'attachable_type' => ScheduledCharge::class,
            'attachable_id' => $scheduledCharge->id,
            'original_name' => 'contrato.pdf',
        ]);
    }
}
