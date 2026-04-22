<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_flows', function (Blueprint $table) {
            $table->boolean('auto_send_enabled')->default(false)->after('status');
            $table->string('auto_send_email')->nullable()->after('auto_send_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('payment_flows', function (Blueprint $table) {
            $table->dropColumn(['auto_send_enabled', 'auto_send_email']);
        });
    }
};
