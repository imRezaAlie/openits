<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('c4_relationships', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('source_id');
            $table->uuid('target_id');
            $table->string('source_type');
            $table->string('target_type');
            $table->string('protocol')->nullable();
            $table->text('description')->nullable();
            $table->boolean('sync')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['source_id', 'source_type']);
            $table->index(['target_id', 'target_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('c4_relationships');
    }
};
