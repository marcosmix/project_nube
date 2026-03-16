<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\Cobros\AdjustmentType;
use App\Enums\Cobros\AdjustmentValueType;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('installment_adjustments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('payment_installment_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('type');

            $table->string('value_type');

            $table->decimal('value', 14, 4);

            $table->decimal('applied_amount', 14, 2);

            $table->text('reason');

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('installment_adjustments');
    }
};