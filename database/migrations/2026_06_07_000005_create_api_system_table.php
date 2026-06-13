<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_system', function (Blueprint $table) {
            $table->id();
            $table->foreignId('api_id')->constrained('apis')->cascadeOnDelete();
            $table->foreignId('system_id')->constrained('systems')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['api_id', 'system_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_system');
    }
};
