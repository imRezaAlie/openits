<?php

namespace Database\Seeders;

use App\Models\Server;
use App\Models\System;
use App\Support\ServerTypes;
use Illuminate\Database\Seeder;

class ServerSeeder extends Seeder
{
    public function run(): void
    {
        $crm = System::where('name', 'CRM System')->first();
        $erp = System::where('name', 'ERP System')->first();
        $payment = System::where('name', 'Payment Gateway')->first();

        if ($crm) {
            $this->seedServers($crm->id, [
                [
                    'name' => 'crm-web-01',
                    'server_type' => ServerTypes::WEB,
                    'hostname' => 'crm-web-01.internal',
                    'ip_address' => '10.0.1.10',
                    'port' => 443,
                    'location' => 'US-East',
                    'ram' => '16 GB',
                    'cpu' => '4 vCPU',
                    'ssl_expires_at' => now()->addMonths(8)->toDateString(),
                    'ssl_issued_at' => now()->subMonths(4)->toDateString(),
                ],
                [
                    'name' => 'crm-app-01',
                    'server_type' => ServerTypes::APPLICATION,
                    'hostname' => 'crm-app-01.internal',
                    'ip_address' => '10.0.1.11',
                    'port' => 8080,
                    'location' => 'US-East',
                    'ram' => '32 GB',
                    'cpu' => '8 vCPU',
                ],
                [
                    'name' => 'crm-db-01',
                    'server_type' => ServerTypes::DATABASE,
                    'hostname' => 'crm-db-01.internal',
                    'ip_address' => '10.0.1.12',
                    'port' => 5432,
                    'location' => 'US-East',
                    'ram' => '64 GB',
                    'cpu' => '16 vCPU',
                ],
                [
                    'name' => 'crm-cache-01',
                    'server_type' => ServerTypes::CACHE,
                    'hostname' => 'crm-cache-01.internal',
                    'ip_address' => '10.0.1.13',
                    'port' => 6379,
                    'location' => 'US-East',
                    'ram' => '8 GB',
                    'cpu' => '2 vCPU',
                ],
            ]);
        }

        if ($erp) {
            $this->seedServers($erp->id, [
                [
                    'name' => 'erp-lb-01',
                    'server_type' => ServerTypes::LOAD_BALANCER,
                    'hostname' => 'erp-lb-01.internal',
                    'ip_address' => '10.0.2.10',
                    'port' => 443,
                    'location' => 'EU-West',
                    'notes' => 'Primary ingress for ERP services',
                ],
                [
                    'name' => 'erp-app-01',
                    'server_type' => ServerTypes::APPLICATION,
                    'hostname' => 'erp-app-01.internal',
                    'ip_address' => '10.0.2.11',
                    'port' => 8080,
                    'location' => 'EU-West',
                    'ram' => '64 GB',
                    'cpu' => '16 vCPU',
                ],
                [
                    'name' => 'erp-db-01',
                    'server_type' => ServerTypes::DATABASE,
                    'hostname' => 'erp-db-01.internal',
                    'ip_address' => '10.0.2.12',
                    'port' => 3306,
                    'location' => 'EU-West',
                    'ram' => '128 GB',
                    'cpu' => '32 vCPU',
                ],
                [
                    'name' => 'erp-kafka-01',
                    'server_type' => ServerTypes::MESSAGE_BROKER,
                    'hostname' => 'erp-kafka-01.internal',
                    'ip_address' => '10.0.2.13',
                    'port' => 9092,
                    'location' => 'EU-West',
                    'ram' => '32 GB',
                    'cpu' => '8 vCPU',
                ],
            ]);
        }

        if ($payment) {
            $this->seedServers($payment->id, [
                [
                    'name' => 'pay-web-01',
                    'server_type' => ServerTypes::WEB,
                    'hostname' => 'pay-web-01.internal',
                    'ip_address' => '10.0.3.10',
                    'port' => 443,
                    'location' => 'US-West',
                    'ssl_expires_at' => now()->addDays(45)->toDateString(),
                    'ssl_issued_at' => now()->subMonths(10)->toDateString(),
                    'notes' => 'SSL certificate expiring soon — renewal scheduled',
                ],
                [
                    'name' => 'pay-app-01',
                    'server_type' => ServerTypes::APPLICATION,
                    'hostname' => 'pay-app-01.internal',
                    'ip_address' => '10.0.3.11',
                    'port' => 3000,
                    'location' => 'US-West',
                    'ram' => '32 GB',
                    'cpu' => '8 vCPU',
                ],
                [
                    'name' => 'pay-cache-01',
                    'server_type' => ServerTypes::CACHE,
                    'hostname' => 'pay-cache-01.internal',
                    'ip_address' => '10.0.3.12',
                    'port' => 6379,
                    'location' => 'US-West',
                    'ram' => '16 GB',
                    'cpu' => '4 vCPU',
                ],
            ]);
        }
    }

    /** @param list<array<string, mixed>> $servers */
    private function seedServers(int $systemId, array $servers): void
    {
        foreach ($servers as $server) {
            Server::updateOrCreate(
                ['system_id' => $systemId, 'name' => $server['name']],
                $server
            );
        }
    }
}
