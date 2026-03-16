<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\Cobros\EmailLogStatus;
use App\Enums\Cobros\EmailTriggerType;
use App\Enums\Cobros\EmailType;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('installment_email_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('payment_flow_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('payment_installment_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();

            $table->string('email_type');

            $table->string('trigger_type');

            $table->string('recipient_email');

            $table->string('subject');

            $table->longText('body');

            $table->string('status')
                ->default(EmailLogStatus::Pending->value);

            $table->text('error_message')->nullable();

            $table->timestamp('sent_at')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(['payment_flow_id']);
            $table->index(['payment_installment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('installment_email_logs');
    }
};