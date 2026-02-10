<?php
namespace Database\Factories;

use App\Models\Contact;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContactFactory extends Factory
{
    protected $model = Contact::class;

    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name'  => fake()->lastName(),
            'email'      => fake()->unique()->safeEmail(),
            'phone'      => fake()->optional()->e164PhoneNumber(),
            'birthdate'  => fake()->optional()->date(),
            'job_title'  => fake()->optional()->jobTitle(),
        ];
    }
}