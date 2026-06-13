<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Vendor;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        $projects = [
            [
                'name' => 'CRM Modernization',
                'vendor' => 'Salesforce',
                'status' => 'active',
            ],
            [
                'name' => 'ERP Integration Hub',
                'vendor' => 'SAP',
                'status' => 'development',
            ],
            [
                'name' => 'Payment Platform Upgrade',
                'vendor' => 'Stripe',
                'status' => 'active',
            ],
            [
                'name' => 'Enterprise Architecture Review',
                'vendor' => 'Microsoft',
                'status' => 'review',
            ],
        ];

        foreach ($projects as $project) {
            $vendor = Vendor::where('name', $project['vendor'])->first();

            if (! $vendor) {
                continue;
            }

            Project::updateOrCreate(
                ['name' => $project['name'], 'vendor_id' => $vendor->id],
                ['status' => $project['status']]
            );
        }
    }
}
