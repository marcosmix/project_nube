<?php

namespace Database\Factories;

use App\Models\Contact;
use App\Models\Developer;
use Illuminate\Database\Eloquent\Factories\Factory;

class DeveloperFactory extends Factory
{
    protected $model = Developer::class;

    public function definition(): array
    {
        return [
            'contact_id' => Contact::factory(),
            'skins' => fake()->randomElements(['Frontend', 'Backend', 'Full Stack', 'Mobile', 'DevOps', 'QA', 'Data', 'AI'], fake()->numberBetween(1, 2)),
            'skills' => fake()->randomElements(['PHP', 'Laravel', 'Livewire', 'React', 'Vue.js', 'Tailwind CSS', 'MySQL', 'Docker'], fake()->numberBetween(2, 4)),
            'github_username' => fake()->userName(),
            'github_url' => fake()->url(),
            'linkedin_url' => fake()->url(),
            'alias' => fake()->unique()->bothify('alias-####'),
            'cbu' => fake()->optional()->numerify('######################'),
            'profile_photo' => null,
            'phrase' => fake()->optional()->sentence(6),
            'score' => fake()->optional()->numberBetween(1, 10),
            'level' => fake()->randomElement(array_keys(Developer::LEVELS)),
            'status' => fake()->randomElement(array_keys(Developer::STATUSES)),
            'availability' => fake()->randomElement(array_keys(Developer::AVAILABILITIES)),
            'notes' => fake()->optional()->paragraph(),
        ];
    }
}
