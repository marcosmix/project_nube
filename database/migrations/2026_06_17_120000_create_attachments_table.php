<?php

use App\Models\Opportunity;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->morphs('attachable');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('label')->nullable();
            $table->string('disk', 40);
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->timestamps();
        });

        if (Schema::hasTable('opportunity_attachments')) {
            $rows = DB::table('opportunity_attachments')->orderBy('id')->get();

            foreach ($rows as $row) {
                DB::table('attachments')->insert([
                    'attachable_type' => Opportunity::class,
                    'attachable_id' => $row->opportunity_id,
                    'uploaded_by' => $row->uploaded_by,
                    'label' => $row->label,
                    'disk' => $row->disk,
                    'path' => $row->path,
                    'original_name' => $row->original_name,
                    'mime_type' => $row->mime_type,
                    'size' => $row->size,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
