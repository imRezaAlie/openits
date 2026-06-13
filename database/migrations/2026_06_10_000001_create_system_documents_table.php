<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('system_id')->constrained('systems')->cascadeOnDelete();
            $table->string('name');
            $table->string('version')->nullable();
            $table->string('attachment_path');
            $table->string('attachment_original_name');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_documents');
    }
};
