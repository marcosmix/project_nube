<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\PaymentReceipt;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentReceiptFactory extends Factory
{
    protected $model = PaymentReceipt::class;

    public function definition(): array
    {
        $originalName = 'recibo-'.fake()->numberBetween(1000, 9999).'.pdf';

        return [
            'payment_id' => Payment::query()->inRandomOrder()->value('id'),
            'uploaded_by' => User::query()->inRandomOrder()->value('id'),
            'disk' => 'public',
            'path' => 'cobros/comprobantes/'.$originalName,
            'original_name' => $originalName,
            'mime_type' => 'application/pdf',
            'size' => fake()->numberBetween(15_000, 400_000),
        ];
    }
}
