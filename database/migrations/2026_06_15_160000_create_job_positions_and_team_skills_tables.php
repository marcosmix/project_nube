<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_positions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('team_skills', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        $now = now();

        $positions = DB::table('contacts')
            ->whereNotNull('job_title')
            ->pluck('job_title')
            ->map(fn (string $name) => preg_replace('/\s+/', ' ', trim($name)) ?? '')
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->map(fn (string $name) => [
                'name' => $name,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ])
            ->all();

        if ($positions !== []) {
            DB::table('job_positions')->insert($positions);
        }

        $skills = collect(config('developers.skills', []))
            ->map(fn (string $name) => preg_replace('/\s+/', ' ', trim($name)) ?? '')
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->map(fn (string $name) => [
                'name' => $name,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ])
            ->all();

        if ($skills !== []) {
            DB::table('team_skills')->insert($skills);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('team_skills');
        Schema::dropIfExists('job_positions');
    }
};
