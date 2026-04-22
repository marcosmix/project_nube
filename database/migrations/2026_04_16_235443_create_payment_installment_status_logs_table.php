<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payment_installment_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_installment_id')->constrained('payment_installments')->cascadeOnDelete();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('from_status', 15)->nullable();
            $table->string('to_status', 15);
            $table->timestamp('changed_at');
            $table->timestamps();

            $table->index(['payment_installment_id', 'changed_at'], 'pisl_installment_changed_at_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_installment_status_logs');
    }
};
