<?php

namespace Database\Seeders;

use App\Models\Technology;
use App\Support\TechnologyCategories as Cat;
use Illuminate\Database\Seeder;

class TechnologySeeder extends Seeder
{
    public function run(): void
    {
        $technologies = [
            ['name' => 'PHP', 'category' => Cat::PROGRAMMING_LANGUAGE, 'icon' => 'fa-brands fa-php'],
            ['name' => 'JavaScript', 'category' => Cat::PROGRAMMING_LANGUAGE, 'icon' => 'fa-brands fa-js'],
            ['name' => 'TypeScript', 'category' => Cat::PROGRAMMING_LANGUAGE, 'icon' => 'fa-brands fa-js'],
            ['name' => 'Python', 'category' => Cat::PROGRAMMING_LANGUAGE, 'icon' => 'fa-brands fa-python'],
            ['name' => 'Java', 'category' => Cat::PROGRAMMING_LANGUAGE, 'icon' => 'fa-brands fa-java'],
            ['name' => 'Go', 'category' => Cat::PROGRAMMING_LANGUAGE, 'icon' => 'fa-brands fa-golang'],
            ['name' => 'C#', 'category' => Cat::PROGRAMMING_LANGUAGE, 'icon' => 'fa-solid fa-code'],
            ['name' => 'Ruby', 'category' => Cat::PROGRAMMING_LANGUAGE, 'icon' => 'fa-solid fa-gem'],
            ['name' => 'Node.js', 'category' => Cat::RUNTIME, 'icon' => 'fa-brands fa-node-js'],
            ['name' => 'JVM', 'category' => Cat::RUNTIME, 'icon' => 'fa-brands fa-java'],
            ['name' => 'Laravel', 'category' => Cat::FRAMEWORK, 'icon' => 'fa-brands fa-laravel'],
            ['name' => 'Spring Boot', 'category' => Cat::FRAMEWORK, 'icon' => 'fa-solid fa-leaf'],
            ['name' => 'Django', 'category' => Cat::FRAMEWORK, 'icon' => 'fa-brands fa-python'],
            ['name' => 'Docker', 'category' => Cat::CONTAINER, 'icon' => 'fa-brands fa-docker'],
            ['name' => 'Kubernetes', 'category' => Cat::ORCHESTRATION, 'icon' => 'fa-solid fa-dharmachakra'],
            ['name' => 'nginx', 'category' => Cat::WEB_SERVER, 'icon' => 'fa-solid fa-server'],
            ['name' => 'Apache', 'category' => Cat::WEB_SERVER, 'icon' => 'fa-solid fa-server'],
            ['name' => 'PostgreSQL', 'category' => Cat::DATABASE, 'icon' => 'fa-solid fa-database'],
            ['name' => 'MySQL', 'category' => Cat::DATABASE, 'icon' => 'fa-solid fa-database'],
            ['name' => 'MongoDB', 'category' => Cat::DATABASE, 'icon' => 'fa-solid fa-database'],
            ['name' => 'Redis', 'category' => Cat::DATABASE, 'icon' => 'fa-solid fa-database'],
            ['name' => 'RabbitMQ', 'category' => Cat::MESSAGING, 'icon' => 'fa-solid fa-envelope'],
            ['name' => 'Kafka', 'category' => Cat::MESSAGING, 'icon' => 'fa-solid fa-stream'],
            ['name' => 'AWS', 'category' => Cat::CLOUD, 'icon' => 'fa-brands fa-aws'],
            ['name' => 'Azure', 'category' => Cat::CLOUD, 'icon' => 'fa-brands fa-microsoft'],
            ['name' => 'GCP', 'category' => Cat::CLOUD, 'icon' => 'fa-brands fa-google'],
        ];

        foreach ($technologies as $tech) {
            Technology::updateOrCreate(
                ['name' => $tech['name'], 'category' => $tech['category']],
                ['icon' => $tech['icon']]
            );
        }
    }
}
