<?php

use App\Enums\Cobros\GenerationMode;
use App\Enums\Cobros\PaymentFlowStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_flows', function (Blueprint $table) {
            $table->id();

            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();

            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();

            $table->string('status')
                ->default(PaymentFlowStatus::Draft->value)
                ->index();

            $table->string('generation_mode')
                ->default(GenerationMode::Automatic->value);

            $table->string('currency', 3)->default(config('cobros.currency', 'ARS'));

            $table->decimal('total_amount', 14, 2)->default(0);
            $table->unsignedInteger('installments_count')->default(1);

            $table->string('payment_frequency')->default('monthly');

            $table->unsignedTinyInteger('billing_day')->nullable();
            $table->unsignedTinyInteger('due_day')->nullable();

            $table->unsignedSmallInteger('grace_days')
                ->default((int) config('cobros.grace_days', 15));

            $table->decimal('interest_daily_rate', 8, 4)
                ->default((float) config('cobros.interest_daily_rate', 0.2));

            $table->date('starts_at')->nullable();
            $table->date('first_due_date')->nullable();

            $table->boolean('email_automation_enabled')
                ->default((bool) data_get(config('cobros.email_automation'), 'enabled', true));

            $table->text('notes')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamp('activated_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('paused_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->timestamps();

            $table->index(['project_id', 'status']);
            $table->index(['client_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_flows');
    }
};