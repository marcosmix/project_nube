<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->foreignId('contact_id')
                ->constrained('contacts')
                ->cascadeOnDelete()
                ->unique();

            $table->string('organization_name');
            $table->string('industry')->nullable();
            $table->string('address')->nullable();
            $table->string('company_logo')->nullable();

            $table->enum('company_size', ['small', 'medium', 'large']);
            $table->unsignedTinyInteger('score')->nullable(); // 1–10
            $table->text('notes')->nullable();

            $table->softDeletes();
            $table->index('organization_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
