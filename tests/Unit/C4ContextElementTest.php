<?php

namespace Tests\Unit;

use App\Models\C4Context;
use App\Models\Domain;
use App\Models\System;
use App\Services\C4ContextElementService;
use App\Services\C4DiagramService;
use App\Services\C4SyncService;
use App\Support\C4ElementTypes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class C4ContextElementTest extends TestCase
{
    use RefreshDatabase;

    public function test_ensure_element_uuids_migrates_legacy_user_ids(): void
    {
        $context = C4Context::create([
            'name' => 'Legacy Context',
            'users' => [['id' => 'user-default', 'name' => 'End User']],
        ]);

        $updated = app(C4ContextElementService::class)->ensureElementUuids($context);

        $this->assertTrue(Str::isUuid($updated->users[0]['id']));
    }

    public function test_resolve_diagram_ids_to_uuids_for_context_relationships(): void
    {
        $domain = Domain::create(['name' => 'Ctx Domain', 'slug' => 'ctx-domain']);
        $system = System::create(['name' => 'Ctx System', 'domain_id' => $domain->id]);
        $context = app(C4SyncService::class)->enableC4ForSystem($system);
        $system->refresh();

        $resolver = app(C4ContextElementService::class);
        $legacyUserId = 'user-default';
        $context->update([
            'users' => [['id' => $legacyUserId, 'name' => 'End User']],
        ]);
        $system->load('c4Context');

        $resolvedUser = $resolver->resolveRelationshipId($system, $legacyUserId, 'source_id');
        $resolvedSystem = $resolver->resolveRelationshipId($system, 'system-'.$system->id, 'target_id');

        $this->assertTrue(Str::isUuid($resolvedUser));
        $this->assertEquals($context->id, $resolvedSystem);
    }

    public function test_context_diagram_uses_uuid_node_ids(): void
    {
        $domain = Domain::create(['name' => 'Diagram Domain', 'slug' => 'diagram-domain']);
        $system = System::create(['name' => 'Diagram System', 'domain_id' => $domain->id]);
        app(C4SyncService::class)->enableC4ForSystem($system);

        $diagram = app(C4DiagramService::class)->buildContextDiagram($system->fresh());

        foreach ($diagram['nodes'] as $node) {
            if (in_array($node['type'], [C4ElementTypes::CONTEXT, C4ElementTypes::USER, C4ElementTypes::EXTERNAL_SYSTEM], true)) {
                $this->assertTrue(Str::isUuid($node['id']), 'Node '.$node['name'].' has non-uuid id: '.$node['id']);
            }
        }
    }
}
