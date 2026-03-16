<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\Cobros\InstallmentStatus;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_installments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('payment_flow_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->unsignedInteger('number');

            $table->string('label')->nullable();

            $table->string('status')
                ->default(InstallmentStatus::Pending->value)
                ->index();

            $table->decimal('scheduled_amount', 14, 2)->default(0);
            $table->decimal('capital_amount', 14, 2)->default(0);

            $table->decimal('interest_deferred_in_amount', 14, 2)->default(0);
            $table->decimal('interest_generated_amount', 14, 2)->default(0);
            $table->decimal('interest_adjustments_amount', 14, 2)->default(0);

            $table->decimal('discounts_amount', 14, 2)->default(0);
            $table->decimal('surcharges_amount', 14, 2)->default(0);

            $table->decimal('paid_amount', 14, 2)->default(0);

            $table->decimal('carried_over_payment_amount', 14, 2)->default(0);

            $table->decimal('outstanding_amount', 14, 2)->default(0);

            $table->decimal('total_due_amount', 14, 2)->default(0);

            $table->date('billing_date')->nullable();
            $table->date('due_date')->nullable();

            $table->date('grace_ends_at')->nullable();
            $table->date('interest_starts_at')->nullable();

            $table->timestamp('paid_at')->nullable();

            $table->timestamp('cancelled_at')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['payment_flow_id', 'number']);
            $table->index(['payment_flow_id', 'status']);
            $table->index('due_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_installments');
    }
};