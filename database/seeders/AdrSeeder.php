<?php

namespace Database\Seeders;

use App\Models\ArchitecturalDecisionRecord;
use App\Models\System;
use App\Models\Technology;
use App\Models\TechnologyRadarEntry;
use App\Models\User;
use App\Support\AdrStatuses;
use App\Support\TechRadarRings;
use Illuminate\Database\Seeder;

class AdrSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        if (! $user) {
            return;
        }

        $system = System::first();
        if ($system) {
            ArchitecturalDecisionRecord::firstOrCreate(
                ['title' => 'Use Laravel for backend services'],
                [
                    'system_id' => $system->id,
                    'author_id' => $user->id,
                    'status' => AdrStatuses::ACCEPTED,
                    'context' => 'We need a productive PHP framework for API and admin features.',
                    'decision' => 'Adopt Laravel 11 as the standard backend framework.',
                    'consequences' => 'Team must maintain PHP 8.2+ skills. Ecosystem plugins available.',
                    'decided_at' => now()->subMonths(3),
                ],
            );
        }

        Technology::all()->each(function (Technology $tech, int $i) use ($user) {
            $rings = TechRadarRings::ALL;
            TechnologyRadarEntry::firstOrCreate(
                ['technology_id' => $tech->id],
                [
                    'ring' => $rings[$i % count($rings)],
                    'notes' => 'Seeded radar position',
                    'updated_by' => $user->id,
                ],
            );
        });
    }
}
