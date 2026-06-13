<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('systems', function (Blueprint $table) {
            $table->foreignId('domain_id')
                ->nullable()
                ->after('vendor_id')
                ->constrained('domains')
                ->nullOnDelete();
        });

        $enterpriseId = DB::table('domains')->where('slug', 'enterprise')->value('id');
        if ($enterpriseId) {
            DB::table('systems')->whereNull('domain_id')->update(['domain_id' => $enterpriseId]);
        }
    }

    public function down(): void
    {
        Schema::table('systems', function (Blueprint $table) {
            $table->dropConstrainedForeignId('domain_id');
        });
    }
};
