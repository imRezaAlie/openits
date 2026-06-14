<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('technologies', function (Blueprint $table) {
            $table->id();
            $table->string('name', 191);
            $table->string('category', 50);
            $table->string('icon')->nullable();
            $table->timestamps();

            $table->unique(['name', 'category']);
        });

        Schema::create('system_technology', function (Blueprint $table) {
            $table->id();
            $table->foreignId('system_id')->constrained('systems')->cascadeOnDelete();
            $table->foreignId('technology_id')->constrained('technologies')->cascadeOnDelete();
            $table->string('version')->nullable();
            $table->timestamps();

            $table->unique(['system_id', 'technology_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_technology');
        Schema::dropIfExists('technologies');
    }
};
