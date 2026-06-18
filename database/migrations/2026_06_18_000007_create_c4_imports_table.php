<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('c4_imports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('system_id')->constrained('systems')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type');
            $table->string('status')->default('pending');
            $table->string('file_path');
            $table->string('original_filename');
            $table->unsignedTinyInteger('progress')->default(0);
            $table->json('result')->nullable();
            $table->text('error_message')->nullable();
            $table->json('options')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['system_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('c4_imports');
    }
};
