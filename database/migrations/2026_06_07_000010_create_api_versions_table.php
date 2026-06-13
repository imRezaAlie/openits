<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('api_id')->constrained('apis')->cascadeOnDelete();
            $table->string('version');
            $table->string('endpoint_url')->nullable();
            $table->text('description')->nullable();
            $table->string('request_format')->nullable();
            $table->string('response_format')->nullable();
            $table->string('authentication_type')->nullable();
            $table->json('protocol_details')->nullable();
            $table->enum('status', ['active', 'deprecated', 'draft'])->default('active');
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->unique(['api_id', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_versions');
    }
};
