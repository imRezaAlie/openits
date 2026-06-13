<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bpmns', function (Blueprint $table) {
            $table->unsignedBigInteger('system_id')->nullable()->after('xml');
            $table->foreign('system_id')->references('id')->on('systems')->cascadeOnDelete();
        });

        Schema::table('bpmns', function (Blueprint $table) {
            $table->unsignedBigInteger('project_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('bpmns', function (Blueprint $table) {
            $table->dropForeign(['system_id']);
            $table->dropColumn('system_id');
        });

        Schema::table('bpmns', function (Blueprint $table) {
            $table->unsignedBigInteger('project_id')->nullable(false)->change();
        });
    }
};
