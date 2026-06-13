<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rest_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('api_id')->constrained('apis')->cascadeOnDelete();
            $table->string('http_method')->default('GET');
            $table->json('request_parameters')->nullable();
            $table->json('response_schema')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rest_details');
    }
};
