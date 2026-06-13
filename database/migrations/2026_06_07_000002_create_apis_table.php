<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('apis', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['rest', 'soap']);
            $table->string('endpoint_url')->nullable();
            $table->text('description')->nullable();
            $table->string('request_format')->nullable();
            $table->string('response_format')->nullable();
            $table->string('authentication_type')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('apis');
    }
};
