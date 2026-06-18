<?php

namespace Tests\Unit;

use App\Models\C4Container;
use App\Models\C4Context;
use App\Models\C4Relationship;
use App\Models\Domain;
use App\Models\System;
use App\Services\C4RelationshipValidator;
use App\Services\C4SyncService;
use App\Support\C4ContainerTypes;
use App\Support\C4ElementTypes;
use App\Support\C4Protocols;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class C4ModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_system_can_have_c4_context(): void
    {
        $domain = Domain::create(['name' => 'Test Domain', 'slug' => 'test-domain']);
        $system = System::create(['name' => 'Test System', 'domain_id' => $domain->id]);

        $context = C4Context::create([
            'name' => 'Test Context',
            'description' => 'Context description',
            'users' => [['id' => 'user-1', 'name' => 'User']],
        ]);

        $system->update(['c4_enabled' => true, 'c4_context_id' => $context->id]);

        $this->assertTrue($system->fresh()->c4_enabled);
        $this->assertEquals($context->id, $system->fresh()->c4Context->id);
    }

    public function test_container_belongs_to_system(): void
    {
        $domain = Domain::create(['name' => 'Test Domain', 'slug' => 'test-domain-2']);
        $system = System::create(['name' => 'API System', 'domain_id' => $domain->id]);

        $container = C4Container::create([
            'system_id' => $system->id,
            'name' => 'Backend',
            'type' => C4ContainerTypes::BACKEND,
            'technology' => 'Laravel',
        ]);

        $this->assertEquals($system->id, $container->system->id);
        $this->assertCount(1, $system->fresh()->c4Containers);
    }

    public function test_relationship_validator_rejects_self_reference(): void
    {
        $validator = app(C4RelationshipValidator::class);

        $this->expectException(RuntimeException::class);
        $validator->validateNoCycle('same-id', 'same-id');
    }

    public function test_relationship_validator_rejects_cycles(): void
    {
        $idA = 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa';
        $idB = 'bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb';
        $idC = 'cccccccc-cccc-cccc-cccc-cccccccccccc';

        C4Relationship::create([
            'source_id' => $idA,
            'target_id' => $idB,
            'source_type' => C4ElementTypes::CONTAINER,
            'target_type' => C4ElementTypes::CONTAINER,
            'protocol' => C4Protocols::REST,
        ]);

        C4Relationship::create([
            'source_id' => $idB,
            'target_id' => $idC,
            'source_type' => C4ElementTypes::CONTAINER,
            'target_type' => C4ElementTypes::CONTAINER,
            'protocol' => C4Protocols::REST,
        ]);

        $validator = app(C4RelationshipValidator::class);

        $this->expectException(RuntimeException::class);
        $validator->validateNoCycle($idC, $idA);
    }

    public function test_sync_service_enables_c4_for_system(): void
    {
        $domain = Domain::create(['name' => 'Sync Domain', 'slug' => 'sync-domain']);
        $system = System::create(['name' => 'Sync System', 'domain_id' => $domain->id]);

        $syncService = app(C4SyncService::class);
        $context = $syncService->enableC4ForSystem($system);

        $system->refresh();
        $this->assertTrue($system->c4_enabled);
        $this->assertNotNull($context->id);
        $this->assertEquals($context->id, $system->c4_context_id);
    }
}
