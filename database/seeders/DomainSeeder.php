<?php

namespace Database\Seeders;

use App\Models\Domain;
use Illuminate\Database\Seeder;

class DomainSeeder extends Seeder
{
    public function run(): void
    {
        $domains = [
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'description' => 'Core business systems — ERP, CRM, HR, finance.',
                'icon' => 'fa-solid fa-building',
                'color' => '#4f46e5',
            ],
            [
                'name' => 'Marketing',
                'slug' => 'marketing',
                'description' => 'Customer engagement, campaigns, analytics, and MarTech.',
                'icon' => 'fa-solid fa-bullhorn',
                'color' => '#ec4899',
            ],
            [
                'name' => 'Network',
                'slug' => 'network',
                'description' => 'Connectivity, routing, DNS, firewalls, and telecom.',
                'icon' => 'fa-solid fa-network-wired',
                'color' => '#0ea5e9',
            ],
            [
                'name' => 'Infrastructure',
                'slug' => 'infrastructure',
                'description' => 'Cloud, compute, storage, containers, and platform services.',
                'icon' => 'fa-solid fa-server',
                'color' => '#64748b',
            ],
        ];

        foreach ($domains as $domain) {
            Domain::updateOrCreate(['slug' => $domain['slug']], $domain);
        }
    }
}
