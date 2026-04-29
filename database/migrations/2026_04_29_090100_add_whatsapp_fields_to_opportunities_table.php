<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('opportunities', function (Blueprint $table) {
            $table->string('whatsapp_contact_id')->nullable()->after('source');
            $table->string('external_conversation_id')->nullable()->after('whatsapp_contact_id');
            $table->timestamp('first_customer_message_at')->nullable()->after('first_contact_at');
            $table->timestamp('last_customer_message_at')->nullable()->after('first_customer_message_at');
            $table->timestamp('customer_service_window_expires_at')->nullable()->after('last_customer_message_at');

            $table->index(['source', 'last_customer_message_at']);
            $table->index('whatsapp_contact_id');
            $table->index('external_conversation_id');
        });
    }

    public function down(): void
    {
        Schema::table('opportunities', function (Blueprint $table) {
            $table->dropIndex(['source', 'last_customer_message_at']);
            $table->dropIndex(['whatsapp_contact_id']);
            $table->dropIndex(['external_conversation_id']);
            $table->dropColumn([
                'whatsapp_contact_id',
                'external_conversation_id',
                'first_customer_message_at',
                'last_customer_message_at',
                'customer_service_window_expires_at',
            ]);
        });
    }
};
