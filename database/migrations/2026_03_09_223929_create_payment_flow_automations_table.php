<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_flow_automations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('payment_flow_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->boolean('is_enabled')->default(true);

            $table->boolean('send_before_due_enabled')->default(true);
            $table->unsignedSmallInteger('send_before_due_days')->default(3);

            $table->boolean('send_on_due_enabled')->default(true);

            $table->boolean('send_on_grace_end_enabled')->default(true);

            $table->boolean('send_on_interest_start_enabled')->default(true);

            $table->timestamp('last_run_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_flow_automations');
    }
};