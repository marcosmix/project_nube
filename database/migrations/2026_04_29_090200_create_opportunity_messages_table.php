<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opportunity_messages', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();

            $table->foreignId('opportunity_id')->constrained('opportunities')->cascadeOnDelete();
            $table->string('direction');
            $table->string('type')->default('unknown');
            $table->text('content')->nullable();
            $table->string('external_message_id')->nullable()->unique();
            $table->json('raw_payload')->nullable();
            $table->timestamp('messaged_at');
            $table->string('status')->nullable();

            $table->index(['opportunity_id', 'messaged_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opportunity_messages');
    }
};
