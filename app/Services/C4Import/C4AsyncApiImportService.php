<?php

namespace App\Services\C4Import;

use App\Models\C4Component;
use App\Models\C4Container;
use App\Models\C4Import;
use App\Models\C4Relationship;
use App\Models\System;
use App\Services\C4SyncService;
use App\Services\C4VersionService;
use App\Support\C4ComponentTypes;
use App\Support\C4ContainerTypes;
use App\Support\C4ElementTypes;
use App\Support\C4Protocols;
use Illuminate\Support\Str;

class C4AsyncApiImportService
{
    use ParsesSpecFiles;

    public function __construct(
        private C4SyncService $syncService,
        private C4VersionService $versionService,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function import(System $system, string $content, C4Import $import): array
    {
        $spec = $this->parseSpecContent($content);
        $import->updateProgress(15);

        if (! isset($spec['asyncapi'])) {
            throw new \InvalidArgumentException('Not a valid AsyncAPI document (missing asyncapi version).');
        }

        $this->syncService->enableC4ForSystem($system);
        $system->refresh();

        $title = $spec['info']['title'] ?? $system->name;
        $import->updateProgress(25);

        $broker = $this->upsertContainer(
            $system,
            C4ContainerTypes::EVENT_BUS,
            $this->detectBroker($spec),
            $this->detectBrokerTechnology($spec),
        );

        $backend = $this->upsertContainer(
            $system,
            C4ContainerTypes::BACKEND,
            $title.' Events',
            'AsyncAPI '.($spec['asyncapi'] ?? '2.0'),
        );

        $this->ensureRelationship($backend->id, $broker->id, C4ElementTypes::CONTAINER, C4ElementTypes::CONTAINER, $this->detectProtocol($spec));
        $import->updateProgress(35);

        $channels = $spec['channels'] ?? [];
        $total = max(1, count($channels));
        $producers = 0;
        $consumers = 0;
        $i = 0;

        foreach ($channels as $channelName => $channel) {
            if (! is_array($channel)) {
                continue;
            }

            $publish = $channel['publish'] ?? null;
            $subscribe = $channel['subscribe'] ?? null;

            if ($publish) {
                $name = ($publish['operationId'] ?? 'Publish'.Str::studly($channelName)).'Producer';
                $component = C4Component::updateOrCreate(
                    ['c4_container_id' => $backend->id, 'name' => $name],
                    [
                        'type' => C4ComponentTypes::PRODUCER,
                        'technology' => $channelName,
                        'description' => $publish['summary'] ?? $publish['description'] ?? null,
                        'metadata' => ['channel' => $channelName, 'direction' => 'publish'],
                    ],
                );
                $this->ensureRelationship(
                    $component->id,
                    $broker->id,
                    C4ElementTypes::COMPONENT,
                    C4ElementTypes::CONTAINER,
                    $this->detectProtocol($spec),
                );
                $producers++;
            }

            if ($subscribe) {
                $name = ($subscribe['operationId'] ?? 'Subscribe'.Str::studly($channelName)).'Consumer';
                $component = C4Component::updateOrCreate(
                    ['c4_container_id' => $backend->id, 'name' => $name],
                    [
                        'type' => C4ComponentTypes::CONSUMER,
                        'technology' => $channelName,
                        'description' => $subscribe['summary'] ?? $subscribe['description'] ?? null,
                        'metadata' => ['channel' => $channelName, 'direction' => 'subscribe'],
                    ],
                );
                $this->ensureRelationship(
                    $broker->id,
                    $component->id,
                    C4ElementTypes::CONTAINER,
                    C4ElementTypes::COMPONENT,
                    $this->detectProtocol($spec),
                );
                $consumers++;
            }

            $i++;
            $import->updateProgress(35 + (int) (($i / $total) * 55));
        }

        $import->updateProgress(95);
        $this->versionService->snapshot($system, 'Imported from AsyncAPI: '.($import->original_filename));

        return [
            'containers' => 2,
            'producers' => $producers,
            'consumers' => $consumers,
            'channels' => count($channels),
            'asyncapi_version' => $spec['asyncapi'],
            'spec_title' => $title,
        ];
    }

    /**
     * @param  array<string, mixed>  $spec
     */
    private function detectBroker(array $spec): string
    {
        $servers = $spec['servers'] ?? [];
        $first = reset($servers);

        if (is_array($first) && isset($first['url'])) {
            $url = $first['url'];
            if (str_contains($url, 'kafka')) {
                return 'Kafka Broker';
            }
            if (str_contains($url, 'rabbitmq') || str_contains($url, 'amqp')) {
                return 'RabbitMQ';
            }
        }

        return 'Message Broker';
    }

    /**
     * @param  array<string, mixed>  $spec
     */
    private function detectBrokerTechnology(array $spec): string
    {
        $protocol = $this->detectProtocol($spec);

        return match ($protocol) {
            C4Protocols::KAFKA => 'Apache Kafka',
            C4Protocols::RABBITMQ => 'RabbitMQ',
            default => 'Event Bus',
        };
    }

    /**
     * @param  array<string, mixed>  $spec
     */
    private function detectProtocol(array $spec): string
    {
        $servers = $spec['servers'] ?? [];
        $first = reset($servers);
        $protocol = is_array($first) ? ($first['protocol'] ?? '') : '';
        $url = is_array($first) ? ($first['url'] ?? '') : '';

        if (str_contains(strtolower($protocol.' '.$url), 'kafka')) {
            return C4Protocols::KAFKA;
        }
        if (str_contains(strtolower($protocol.' '.$url), 'amqp') || str_contains(strtolower($url), 'rabbit')) {
            return C4Protocols::RABBITMQ;
        }

        $bindings = $spec['bindings'] ?? [];

        return C4Protocols::KAFKA;
    }

    private function upsertContainer(System $system, string $type, string $name, ?string $technology): C4Container
    {
        return C4Container::updateOrCreate(
            ['system_id' => $system->id, 'type' => $type, 'name' => $name],
            ['technology' => $technology, 'metadata' => ['imported_from' => 'asyncapi']],
        );
    }

    private function ensureRelationship(
        string $sourceId,
        string $targetId,
        string $sourceType,
        string $targetType,
        string $protocol,
    ): void {
        C4Relationship::firstOrCreate(
            [
                'source_id' => $sourceId,
                'target_id' => $targetId,
                'source_type' => $sourceType,
                'target_type' => $targetType,
            ],
            [
                'protocol' => $protocol,
                'sync' => false,
                'description' => 'Imported from AsyncAPI',
            ],
        );
    }
}
