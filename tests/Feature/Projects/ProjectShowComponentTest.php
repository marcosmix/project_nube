<?php

namespace Tests\Feature\Projects;

use App\Enums\ProjectStatus;
use App\Livewire\Projects\Show as ProjectsShow;
use App\Models\Client;
use App\Models\Opportunity;
use App\Models\Project;
use App\Models\ProjectStatusLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ProjectShowComponentTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_start_an_operation_from_sale_closed(): void
    {
        $user = User::factory()->create();
        $project = $this->makeProject(ProjectStatus::SaleClosed, $user);

        $this->actingAs($user);

        Livewire::test(ProjectsShow::class, ['project' => $project])
            ->call('openStatusConfirmation', ProjectStatus::Execution->value)
            ->assertSet('pendingStatus', ProjectStatus::Execution->value)
            ->assertSet('showStatusConfirmationModal', true)
            ->call('confirmStatusChange')
            ->assertSet('showStatusConfirmationModal', false)
            ->assertSet('pendingStatus', null);

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'status' => ProjectStatus::Execution->value,
        ]);

        $this->assertDatabaseHas('project_status_logs', [
            'project_id' => $project->id,
            'status' => ProjectStatus::Execution->value,
            'by_user_id' => $user->id,
        ]);
    }

    public function test_it_can_upload_an_attachment_for_the_operation(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $project = $this->makeProject(ProjectStatus::SaleClosed, $user);

        $this->actingAs($user);

        Livewire::test(ProjectsShow::class, ['project' => $project])
            ->set('attachmentLabel', 'Acta de inicio')
            ->set('attachmentUpload', UploadedFile::fake()->create('acta.pdf', 256, 'application/pdf'))
            ->call('saveAttachment')
            ->assertSet('attachmentLabel', '');

        $this->assertDatabaseHas('attachments', [
            'attachable_type' => Project::class,
            'attachable_id' => $project->id,
            'label' => 'Acta de inicio',
            'original_name' => 'acta.pdf',
        ]);
    }

    private function makeProject(ProjectStatus $status, User $user): Project
    {
        $client = Client::factory()->create();
        $opportunity = Opportunity::factory()->create([
            'client_id' => $client->id,
        ]);
        $project = Project::query()->create([
            'name' => 'Operacion de prueba',
            'status' => $status->value,
            'client_id' => $client->id,
            'opportunity_id' => $opportunity->id,
            'total_cost' => 120000,
        ]);

        ProjectStatusLog::create([
            'project_id' => $project->id,
            'status' => $status->value,
            'by_user_id' => $user->id,
            'created_at' => now(),
        ]);

        return $project;
    }
}
