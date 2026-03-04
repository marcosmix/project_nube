<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('project_notes', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->text('content');

            // opcional: guardar estado al momento de escribir la nota
            $table->string('status')->nullable();

            $table->foreignId('by_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->index(['project_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_notes');
    }
};
