<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ldap_logs', function (Blueprint $table) {
            $table->id();
            $table->string('username')->nullable();
            $table->string('domain')->nullable();
            $table->string('action');
            $table->string('status');
            $table->string('ip_address', 45)->nullable();
            $table->text('message')->nullable();
            $table->json('context')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['action', 'status']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ldap_logs');
    }
};
