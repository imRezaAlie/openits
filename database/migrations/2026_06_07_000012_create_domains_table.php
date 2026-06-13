<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('domains', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->string('color', 20)->nullable();
            $table->timestamps();
        });

        $now = now();
        DB::table('domains')->insert([
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'description' => 'Core business systems — ERP, CRM, HR, finance.',
                'icon' => 'fa-solid fa-building',
                'color' => '#4f46e5',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Marketing',
                'slug' => 'marketing',
                'description' => 'Customer engagement, campaigns, analytics, and MarTech.',
                'icon' => 'fa-solid fa-bullhorn',
                'color' => '#ec4899',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Network',
                'slug' => 'network',
                'description' => 'Connectivity, routing, DNS, firewalls, and telecom.',
                'icon' => 'fa-solid fa-network-wired',
                'color' => '#0ea5e9',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Infrastructure',
                'slug' => 'infrastructure',
                'description' => 'Cloud, compute, storage, containers, and platform services.',
                'icon' => 'fa-solid fa-server',
                'color' => '#64748b',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('domains');
    }
};
