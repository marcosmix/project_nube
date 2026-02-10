<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Contact;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition(): array
    {
        return [
            'contact_id' => Contact::factory(),
            'organization_name' => fake()->company(),
            'industry' => fake()->randomElement([
                'Tecnología', 'Comercio', 'Construcción', 'Servicios', 'Salud',
                'Educación', 'Gastronomía', 'Agro', 'Minería', 'Turismo',
            ]),
            'address' => fake()->optional()->address(),
            'company_logo' => null,

            // ✅ coincide con tu enum
            'company_size' => fake()->randomElement(['small', 'medium', 'large']),

            'score' => fake()->optional()->numberBetween(1, 10),
            'notes' => fake()->optional()->sentence(12),
        ];
    }
}
