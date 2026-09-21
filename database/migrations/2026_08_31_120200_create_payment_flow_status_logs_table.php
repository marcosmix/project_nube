<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payment_flow_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_flow_id')->constrained()->cascadeOnDelete();
            $table->string('from_status', 15)->nullable();
            $table->string('to_status', 15);
            $table->text('reason')->nullable();
            $table->foreignId('by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('changed_at');
            $table->index(['payment_flow_id', 'changed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_flow_status_logs');
    }
};
