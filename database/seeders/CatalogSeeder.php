<?php

namespace Database\Seeders;

use App\Models\JobPosition;
use App\Models\TeamSkill;
use Illuminate\Database\Seeder;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        if (JobPosition::query()->count() < 40) {
            JobPosition::factory()->count(40 - JobPosition::query()->count())->create();
        }

        $skillPool = [
            'PHP', 'Laravel', 'Livewire', 'Symfony', 'Node.js', 'NestJS', 'React', 'Vue.js', 'Angular', 'Next.js',
            'Tailwind CSS', 'MySQL', 'PostgreSQL', 'MariaDB', 'MongoDB', 'Redis', 'Flutter', 'React Native',
            'Swift', 'Kotlin', 'Docker', 'AWS', 'Cloudflare', 'Nginx', 'DevOps', 'QA', 'AI', 'Python', 'Go',
            'TypeScript', 'JavaScript', 'CI/CD', 'Linux', 'Figma', 'Testing', 'Data', 'Scrum', 'UX', 'Security', 'API Design',
        ];

        foreach ($skillPool as $skillName) {
            TeamSkill::query()->firstOrCreate(['name' => $skillName], ['is_active' => true]);
        }

        if (TeamSkill::query()->count() < 40) {
            TeamSkill::factory()->count(40 - TeamSkill::query()->count())->create();
        }
    }
}
