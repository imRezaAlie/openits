<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('apis', function (Blueprint $table) {
            $table->foreignId('owner_system_id')->nullable()->after('authentication_type')->constrained('systems')->nullOnDelete();
        });

        $ownerRows = \Illuminate\Support\Facades\DB::table('api_system')
            ->select('api_id', 'system_id')
            ->orderBy('id')
            ->get()
            ->unique('api_id');

        foreach ($ownerRows as $row) {
            \Illuminate\Support\Facades\DB::table('apis')
                ->where('id', $row->api_id)
                ->whereNull('owner_system_id')
                ->update(['owner_system_id' => $row->system_id]);
        }
    }

    public function down(): void
    {
        Schema::table('apis', function (Blueprint $table) {
            $table->dropConstrainedForeignId('owner_system_id');
        });
    }
};
