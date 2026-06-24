<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE apis MODIFY type VARCHAR(20) NOT NULL');
        } else {
            Schema::table('apis', function (Blueprint $table) {
                $table->string('type', 20)->change();
            });
        }

        Schema::table('apis', function (Blueprint $table) {
            if (! Schema::hasColumn('apis', 'protocol_details')) {
                $table->json('protocol_details')->nullable()->after('authentication_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('apis', function (Blueprint $table) {
            $table->dropColumn('protocol_details');
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE apis MODIFY type ENUM('rest', 'soap') NOT NULL");
        }
    }
};
