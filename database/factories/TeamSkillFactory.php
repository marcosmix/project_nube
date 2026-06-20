<?php

namespace Database\Factories;

use App\Models\TeamSkill;
use Illuminate\Database\Eloquent\Factories\Factory;

class TeamSkillFactory extends Factory
{
    protected $model = TeamSkill::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement([
                'PHP', 'Laravel', 'Livewire', 'Symfony', 'Node.js', 'NestJS', 'React', 'Vue.js', 'Angular', 'Next.js',
                'Tailwind CSS', 'MySQL', 'PostgreSQL', 'MariaDB', 'MongoDB', 'Redis', 'Flutter', 'React Native',
                'Swift', 'Kotlin', 'Docker', 'AWS', 'Cloudflare', 'Nginx', 'DevOps', 'QA', 'AI', 'Python', 'Go',
                'TypeScript', 'JavaScript', 'CI/CD', 'Linux', 'Figma', 'Testing', 'Data', 'Scrum', 'UX', 'Security', 'API Design',
            ]),
            'is_active' => fake()->boolean(90),
        ];
    }
}
