<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bpmns', function (Blueprint $table) {
            $table->string('diagram_type', 20)->default('bpmn')->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('bpmns', function (Blueprint $table) {
            $table->dropColumn('diagram_type');
        });
    }
};
