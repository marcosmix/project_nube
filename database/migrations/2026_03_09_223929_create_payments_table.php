<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\Cobros\PaymentStatus;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('payment_flow_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('payment_installment_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('status')
                ->default(PaymentStatus::Recorded->value);

            $table->string('payment_method');

            $table->decimal('amount', 14, 2);

            $table->decimal('capital_applied_amount', 14, 2)->default(0);
            $table->decimal('interest_applied_amount', 14, 2)->default(0);

            $table->decimal('surcharge_applied_amount', 14, 2)->default(0);
            $table->decimal('discount_applied_amount', 14, 2)->default(0);

            $table->decimal('carried_forward_amount', 14, 2)->default(0);

            $table->timestamp('paid_at');

            $table->string('receipt_path')->nullable();

            $table->string('reference')->nullable();

            $table->text('notes')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('reversed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('reversed_at')->nullable();

            $table->text('reversal_reason')->nullable();

            $table->timestamps();

            $table->index(['payment_installment_id']);
            $table->index(['payment_flow_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};