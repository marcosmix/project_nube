<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\Developer;
use App\Models\JobPosition;
use App\Models\TeamSkill;
use Illuminate\Database\Seeder;

class TeamSeeder extends Seeder
{
    public function run(): void
    {
        $positions = JobPosition::query()->where('is_active', true)->pluck('name')->all();
        $skills = TeamSkill::query()->where('is_active', true)->pluck('name')->all();

        $existing = Developer::query()->count();
        $needed = max(40 - $existing, 0);

        for ($i = 0; $i < $needed; $i++) {
            $contact = Contact::factory()->create([
                'job_title' => fake()->randomElement($positions),
            ]);

            Developer::factory()->create([
                'contact_id' => $contact->id,
                'skills' => fake()->randomElements($skills, fake()->numberBetween(2, min(5, count($skills)))),
                'status' => fake()->boolean(85) ? 'active' : 'inactive',
            ]);
        }
    }
}
