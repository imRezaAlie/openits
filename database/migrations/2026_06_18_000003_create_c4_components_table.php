<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('c4_components', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('c4_container_id');
            $table->string('name');
            $table->string('type');
            $table->string('technology')->nullable();
            $table->text('description')->nullable();
            $table->json('dependencies')->nullable();
            $table->json('position')->nullable();
            $table->json('metadata')->nullable();
            $table->date('sunset_date')->nullable();
            $table->timestamps();

            $table->foreign('c4_container_id')->references('id')->on('c4_containers')->cascadeOnDelete();
            $table->index(['c4_container_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('c4_components');
    }
};
