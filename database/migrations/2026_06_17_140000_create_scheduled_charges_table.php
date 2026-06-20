<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scheduled_charges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reference_name');
            $table->text('detail')->nullable();
            $table->decimal('amount', 12, 2);
            $table->date('charge_date');
            $table->string('frequency', 20);
            $table->string('status', 20)->default('active');
            $table->timestamps();

            $table->index(['client_id', 'status']);
            $table->index(['charge_date', 'frequency']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scheduled_charges');
    }
};
