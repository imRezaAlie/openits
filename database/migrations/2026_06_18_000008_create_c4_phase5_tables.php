<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('c4_change_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('system_id')->constrained('systems')->cascadeOnDelete();
            $table->foreignId('requester_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->text('description');
            $table->text('impact')->nullable();
            $table->string('status')->default('draft');
            $table->text('reviewer_notes')->nullable();
            $table->json('snapshot')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['system_id', 'status']);
        });

        Schema::create('technology_radar_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('technology_id')->constrained('technologies')->cascadeOnDelete();
            $table->string('ring')->default('assess');
            $table->text('notes')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique('technology_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('technology_radar_entries');
        Schema::dropIfExists('c4_change_requests');
    }
};
