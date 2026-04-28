<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opportunity_notes', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->foreignId('opportunity_id')->constrained('opportunities')->cascadeOnDelete();
            $table->foreignId('by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('content');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opportunity_notes');
    }
};
