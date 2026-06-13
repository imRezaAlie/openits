<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rest_details', function (Blueprint $table) {
            $table->json('openapi_spec')->nullable()->after('response_schema');
        });

        Schema::table('soap_details', function (Blueprint $table) {
            $table->json('operation_spec')->nullable()->after('method_name');
        });
    }

    public function down(): void
    {
        Schema::table('rest_details', function (Blueprint $table) {
            $table->dropColumn('openapi_spec');
        });

        Schema::table('soap_details', function (Blueprint $table) {
            $table->dropColumn('operation_spec');
        });
    }
};
