<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('project_amount_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->decimal('old_amount', 12, 2)->nullable();
            $table->decimal('new_amount', 12, 2);
            $table->text('reason')->nullable();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index('project_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_amount_histories');
    }
};
