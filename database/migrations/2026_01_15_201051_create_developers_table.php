<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('developers', function (Blueprint $table) {
            $table->id();

            $table
                ->foreignId('contact_id')
                ->constrained('contacts')
                ->cascadeOnDelete()
                ->unique();

            $table->json('skins')->nullable();
            $table->json('skills')->nullable();

            $table->string('github_username')->nullable();
            $table->string('github_url')->nullable();
            $table->string('linkedin_url')->nullable();

            $table->string('alias')->nullable();
            $table->string('cbu')->nullable();
            $table->string('profile_photo')->nullable();
            $table->string('phrase')->nullable();

            $table->unsignedTinyInteger('score')->nullable();
            $table->enum('level', ['junior', 'semi_senior', 'senior', 'lead'])->default('junior');

            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->enum('availability', ['full_time', 'freelance'])->default('full_time');

            $table->text('notes')->nullable();

            $table->softDeletes();
            $table->timestamps();

            $table->index(['status', 'availability', 'level']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('developers');
    }
};
