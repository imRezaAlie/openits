<?php

namespace Database\Seeders;

use App\Models\Vendor;
use Illuminate\Database\Seeder;

class VendorSeeder extends Seeder
{
    public function run(): void
    {
        $vendors = [
            'Salesforce',
            'SAP',
            'Stripe',
            'Microsoft',
            'Amazon Web Services',
            'Google Cloud',
        ];

        foreach ($vendors as $name) {
            Vendor::updateOrCreate(['name' => $name]);
        }
    }
}
