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
        Schema::table('users', function (Blueprint $table) {
            $table->string('ldap_username')->nullable()->index();
            $table->string('ldap_domain')->nullable();
            $table->string('ldap_samaccountname')->nullable()->index();
            $table->string('ldap_distinguished_name')->nullable();
            $table->json('ldap_groups')->nullable();
            $table->timestamp('ldap_last_sync_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'ldap_username',
                'ldap_domain',
                'ldap_samaccountname',
                'ldap_distinguished_name',
                'ldap_groups',
                'ldap_last_sync_at',
            ]);
        });
    }
};
