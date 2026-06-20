<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\Developer;
use App\Models\JobPosition;
use App\Models\TeamSkill;
use Illuminate\Database\Seeder;

class DeveloperSeeder extends Seeder
{
    public function run(): void
    {
        $positions = JobPosition::query()->where('is_active', true)->pluck('name')->all();
        $skills = TeamSkill::query()->where('is_active', true)->pluck('name')->all();

        for ($i = Developer::query()->count(); $i < 40; $i++) {
            $contact = Contact::factory()->create([
                'job_title' => fake()->randomElement($positions),
            ]);

            Developer::factory()->create([
                'contact_id' => $contact->id,
                'skills' => fake()->randomElements($skills, fake()->numberBetween(2, min(5, count($skills)))),
            ]);
        }
    }
}
