<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('developer_project', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('developer_id')->constrained('developers')->cascadeOnDelete();

            $table->unique(['project_id', 'developer_id']);
            $table->index(['developer_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('developer_project');
    }
};
