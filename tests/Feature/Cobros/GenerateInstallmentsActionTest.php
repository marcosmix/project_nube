<?php

use App\Actions\Cobros\GenerateInstallmentsAction;
use App\Enums\Cobros\GenerationMode;
use App\Enums\Cobros\PaymentFlowStatus;
use App\Models\Client;
use App\Models\Contact;
use App\Models\PaymentFlow;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('genera las cuotas automáticas correctamente', function () {
    // Arrange
    $user = User::factory()->create();

    $contact = Contact::query()->create([
        'first_name' => 'Marcos',
        'last_name' => 'Caballero',
        'email' => 'marcos@test.com',
        'phone' => '2640000000',
    ]);

    $client = Client::query()->create([
        'contact_id' => $contact->id,
        'organization_name' => 'Nube SRL',
        'industry' => 'Software',
        'company_size' => 'small',
        'score' => 8,
    ]);

    $project = Project::query()->create([
        'name' => 'ERP Nube',
        'status' => 'sale_closed',
        'client_id' => $client->id,
    ]);

    $flow = PaymentFlow::query()->create([
        'project_id' => $project->id,
        'client_id' => $client->id,
        'code' => 'COB-2026-0001',
        'name' => 'Plan de cobro ERP Nube',
        'status' => PaymentFlowStatus::Draft,
        'generation_mode' => GenerationMode::Automatic,
        'currency' => 'ARS',
        'total_amount' => 100000,
        'installments_count' => 3,
        'payment_frequency' => 'monthly',
        'billing_day' => 5,
        'due_day' => 10,
        'grace_days' => 15,
        'interest_daily_rate' => 0.2,
        'first_due_date' => '2026-04-10',
        'created_by' => $user->id,
    ]);

    // Act
    $installments = app(GenerateInstallmentsAction::class)->execute($flow);

    // Assert
    expect($installments)->toHaveCount(3);

    $flow->refresh();
    $dbInstallments = $flow->installments()->orderBy('number')->get();

    expect($dbInstallments)->toHaveCount(3);

    expect((float) $dbInstallments[0]->capital_amount)->toBe(33333.33);
    expect((float) $dbInstallments[1]->capital_amount)->toBe(33333.33);
    expect((float) $dbInstallments[2]->capital_amount)->toBe(33333.34);

    expect($dbInstallments[0]->number)->toBe(1);
    expect($dbInstallments[1]->number)->toBe(2);
    expect($dbInstallments[2]->number)->toBe(3);

    expect($dbInstallments[0]->due_date->format('Y-m-d'))->toBe('2026-04-10');
    expect($dbInstallments[1]->due_date->format('Y-m-d'))->toBe('2026-05-10');
    expect($dbInstallments[2]->due_date->format('Y-m-d'))->toBe('2026-06-10');

    expect($dbInstallments[0]->billing_date->format('Y-m-d'))->toBe('2026-04-05');
    expect($dbInstallments[1]->billing_date->format('Y-m-d'))->toBe('2026-05-05');
    expect($dbInstallments[2]->billing_date->format('Y-m-d'))->toBe('2026-06-05');

    expect($dbInstallments[0]->grace_ends_at->format('Y-m-d'))->toBe('2026-04-25');
    expect($dbInstallments[0]->interest_starts_at->format('Y-m-d'))->toBe('2026-04-26');

    expect((float) $dbInstallments->sum('capital_amount'))->toBe(100000.00);
});