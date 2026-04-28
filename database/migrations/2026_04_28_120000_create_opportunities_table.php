<?php

use App\Enums\Sales\OpportunitySource;
use App\Enums\Sales\OpportunityStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opportunities', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();

            $table->foreignId('client_id')->nullable()->constrained('clients')->nullOnDelete();
            $table->foreignId('responsible_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('name');
            $table->string('status')->default(OpportunityStatus::New->value);
            $table->string('source')->default(OpportunitySource::Manual->value);
            $table->date('first_contact_at')->nullable();

            $table->string('contact_name')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_handle')->nullable();
            $table->text('initial_message')->nullable();

            $table->index(['status', 'source']);
            $table->index('first_contact_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opportunities');
    }
};
