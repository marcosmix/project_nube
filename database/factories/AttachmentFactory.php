<?php

namespace Database\Factories;

use App\Models\Attachment;
use App\Models\Opportunity;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttachmentFactory extends Factory
{
    protected $model = Attachment::class;

    public function definition(): array
    {
        $filename = fake()->uuid().'.pdf';

        return [
            'attachable_type' => Opportunity::class,
            'attachable_id' => Opportunity::query()->inRandomOrder()->value('id'),
            'uploaded_by' => User::query()->inRandomOrder()->value('id'),
            'label' => fake()->randomElement(['Propuesta comercial', 'Alcance', 'Presupuesto', 'Presentacion']),
            'disk' => 'public',
            'path' => 'ventas/propuestas/'.$filename,
            'original_name' => 'propuesta-'.fake()->numberBetween(100, 999).'.pdf',
            'mime_type' => 'application/pdf',
            'size' => fake()->numberBetween(25_000, 950_000),
        ];
    }
}
