<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rest_details', function (Blueprint $table) {
            $table->foreignId('api_version_id')->nullable()->after('id')->constrained('api_versions')->cascadeOnDelete();
        });

        Schema::table('soap_details', function (Blueprint $table) {
            $table->foreignId('api_version_id')->nullable()->after('id')->constrained('api_versions')->cascadeOnDelete();
        });

        $apis = DB::table('apis')->orderBy('id')->get();

        foreach ($apis as $api) {
            $versionId = DB::table('api_versions')->insertGetId([
                'api_id' => $api->id,
                'version' => '1.0.0',
                'endpoint_url' => $api->endpoint_url,
                'description' => $api->description,
                'request_format' => $api->request_format,
                'response_format' => $api->response_format,
                'authentication_type' => $api->authentication_type,
                'protocol_details' => $api->protocol_details,
                'status' => 'active',
                'is_default' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('rest_details')->where('api_id', $api->id)->update(['api_version_id' => $versionId]);
            DB::table('soap_details')->where('api_id', $api->id)->update(['api_version_id' => $versionId]);
        }

        Schema::table('rest_details', function (Blueprint $table) {
            $table->dropForeign(['api_id']);
            $table->dropColumn('api_id');
        });

        Schema::table('soap_details', function (Blueprint $table) {
            $table->dropForeign(['api_id']);
            $table->dropColumn('api_id');
        });
    }

    public function down(): void
    {
        Schema::table('rest_details', function (Blueprint $table) {
            $table->foreignId('api_id')->nullable()->after('id')->constrained('apis')->cascadeOnDelete();
        });

        Schema::table('soap_details', function (Blueprint $table) {
            $table->foreignId('api_id')->nullable()->after('id')->constrained('apis')->cascadeOnDelete();
        });

        $versions = DB::table('api_versions')->where('is_default', true)->get();

        foreach ($versions as $version) {
            $api = DB::table('apis')->where('id', $version->api_id)->first();
            if (! $api) {
                continue;
            }

            DB::table('apis')->where('id', $version->api_id)->update([
                'endpoint_url' => $version->endpoint_url,
                'description' => $version->description ?? $api->description,
                'request_format' => $version->request_format,
                'response_format' => $version->response_format,
                'authentication_type' => $version->authentication_type,
                'protocol_details' => $version->protocol_details,
            ]);

            DB::table('rest_details')->where('api_version_id', $version->id)->update(['api_id' => $version->api_id]);
            DB::table('soap_details')->where('api_version_id', $version->id)->update(['api_id' => $version->api_id]);
        }

        Schema::table('rest_details', function (Blueprint $table) {
            $table->dropForeign(['api_version_id']);
            $table->dropColumn('api_version_id');
        });

        Schema::table('soap_details', function (Blueprint $table) {
            $table->dropForeign(['api_version_id']);
            $table->dropColumn('api_version_id');
        });

        Schema::dropIfExists('api_versions');
    }
};
