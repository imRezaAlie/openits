<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('systems', function (Blueprint $table) {
            $table->boolean('c4_enabled')->default(false)->after('icon');
            $table->uuid('c4_context_id')->nullable()->after('c4_enabled');
        });

        Schema::table('systems', function (Blueprint $table) {
            $table->foreign('c4_context_id')->references('id')->on('c4_contexts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('systems', function (Blueprint $table) {
            $table->dropForeign(['c4_context_id']);
            $table->dropColumn(['c4_enabled', 'c4_context_id']);
        });
    }
};
