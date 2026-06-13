<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tps_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('api_id')->constrained('apis')->cascadeOnDelete();
            $table->decimal('tps_value', 12, 2);
            $table->timestamp('recorded_at');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['api_id', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tps_metrics');
    }
};
