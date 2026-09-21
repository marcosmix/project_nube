<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancelled_reason')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
        });

        Schema::table('payment_flows', function (Blueprint $table) {
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancelled_reason')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('payment_flows', function (Blueprint $table) {
            $table->dropForeign(['cancelled_by']);
            $table->dropColumn(['cancelled_at', 'cancelled_reason', 'cancelled_by']);
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropForeign(['cancelled_by']);
            $table->dropColumn(['cancelled_at', 'cancelled_reason', 'cancelled_by']);
        });
    }
};
