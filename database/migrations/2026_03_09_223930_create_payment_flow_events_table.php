<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\Cobros\PaymentFlowEventType;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_flow_events', function (Blueprint $table) {
            $table->id();

            $table->foreignId('payment_flow_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('event_type');

            $table->string('title');

            $table->text('description')->nullable();

            $table->json('meta')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_flow_events');
    }
};