<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->softDeletes();

            $table->string('name');

            // status: prospection | interested | sale_closed | execution | paused | finished
            $table->string('status')->default('prospection');

            // execution_sub_status: on_track | with_debt | delayed
            $table->string('execution_sub_status')->nullable();

            $table->foreignId('client_id')
                ->constrained('clients')
                ->cascadeOnDelete();

            // Prospección
            $table->text('prospection_notes')->nullable();

            // Comercial / Finanzas
            $table->string('proposal_url')->nullable();
            $table->string('excel_url')->nullable();
            $table->unsignedBigInteger('total_cost')->nullable();
            $table->unsignedInteger('installments')->nullable();
            $table->date('estimated_start_date')->nullable();
            $table->date('estimated_end_date')->nullable();

            // Ejecución
            $table->unsignedTinyInteger('sprint_close_day')->nullable();
            $table->date('actual_start_date')->nullable();
            $table->date('actual_end_date')->nullable();

            // Pausa
            $table->text('pause_reason')->nullable();
            $table->timestamp('paused_at')->nullable();

            $table->index(['status', 'execution_sub_status']);
            $table->index('client_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};