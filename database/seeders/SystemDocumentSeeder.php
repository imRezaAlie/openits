<?php

namespace Database\Seeders;

use App\Models\System;
use App\Models\SystemDocument;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class SystemDocumentSeeder extends Seeder
{
    public function run(): void
    {
        $documents = [
            'CRM System' => [
                [
                    'name' => 'Integration Guide',
                    'version' => '2.1.0',
                    'filename' => 'crm-integration-guide.md',
                    'content' => "# CRM Integration Guide\n\nCovers REST and GraphQL endpoints for customer data access.\n",
                ],
                [
                    'name' => 'Runbook',
                    'version' => '1.0.0',
                    'filename' => 'crm-runbook.md',
                    'content' => "# CRM Operations Runbook\n\nIncident response and escalation procedures.\n",
                ],
            ],
            'ERP System' => [
                [
                    'name' => 'SOAP API Reference',
                    'version' => '3.4.0',
                    'filename' => 'erp-soap-reference.md',
                    'content' => "# ERP SOAP API Reference\n\nWSDL endpoints and order processing operations.\n",
                ],
            ],
            'Payment Gateway' => [
                [
                    'name' => 'PCI Compliance Checklist',
                    'version' => '2026.1',
                    'filename' => 'payment-pci-checklist.md',
                    'content' => "# PCI Compliance Checklist\n\nAnnual audit items for payment processing infrastructure.\n",
                ],
            ],
        ];

        foreach ($documents as $systemName => $systemDocs) {
            $system = System::where('name', $systemName)->first();

            if (! $system) {
                continue;
            }

            foreach ($systemDocs as $doc) {
                $path = "system-documents/{$system->id}/{$doc['filename']}";

                Storage::disk('local')->put($path, $doc['content']);

                SystemDocument::updateOrCreate(
                    [
                        'system_id' => $system->id,
                        'name' => $doc['name'],
                        'version' => $doc['version'],
                    ],
                    [
                        'attachment_path' => $path,
                        'attachment_original_name' => $doc['filename'],
                    ]
                );
            }
        }
    }
}
