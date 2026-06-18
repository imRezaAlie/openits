<?php

namespace Tests\Feature;

use App\Models\Domain;
use App\Models\System;
use App\Models\User;
use App\Services\C4DiagramService;
use App\Services\C4SyncService;
use App\Support\C4ElementTypes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class C4ControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_c4_index_requires_auth(): void
    {
        $this->get(route('c4.index'))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_c4_index(): void
    {
        $this->actingAs($this->user)
            ->get(route('c4.index'))
            ->assertOk()
            ->assertSee('C4 Architecture Models');
    }

    public function test_user_can_enable_and_view_context_diagram(): void
    {
        $domain = Domain::create(['name' => 'Feature Domain', 'slug' => 'feature-domain']);
        $system = System::create(['name' => 'Feature System', 'domain_id' => $domain->id]);

        $this->actingAs($this->user)
            ->post(route('c4.systems.enable', $system))
            ->assertRedirect(route('c4.systems.context', $system));

        $this->actingAs($this->user)
            ->get(route('c4.systems.context', $system))
            ->assertOk()
            ->assertSee('Feature System')
            ->assertSee('Context');
    }

    public function test_diagram_data_endpoint_returns_json(): void
    {
        $domain = Domain::create(['name' => 'API Domain', 'slug' => 'api-domain']);
        $system = System::create(['name' => 'API System', 'domain_id' => $domain->id]);

        app(C4SyncService::class)->enableC4ForSystem($system);

        $this->actingAs($this->user)
            ->getJson(route('c4.diagram.data', ['system_id' => $system->id, 'level' => 'context']))
            ->assertOk()
            ->assertJsonStructure(['level', 'system', 'nodes', 'edges']);
    }

    public function test_export_structurizr_dsl(): void
    {
        $domain = Domain::create(['name' => 'Export Domain', 'slug' => 'export-domain']);
        $system = System::create(['name' => 'Export System', 'domain_id' => $domain->id]);

        app(C4SyncService::class)->enableC4ForSystem($system);

        $response = $this->actingAs($this->user)
            ->get(route('c4.systems.export', [$system, 'format' => 'structurizr']));

        $response->assertOk();
        $this->assertStringContainsString('workspace {', $response->getContent());
    }

    public function test_context_relationship_accepts_diagram_element_ids(): void
    {
        $domain = Domain::create(['name' => 'Rel Domain', 'slug' => 'rel-domain']);
        $system = System::create(['name' => 'Rel System', 'domain_id' => $domain->id]);
        app(C4SyncService::class)->enableC4ForSystem($system);

        $diagram = app(C4DiagramService::class)->buildContextDiagram($system->fresh());
        $userNode = collect($diagram['nodes'])->firstWhere('type', C4ElementTypes::USER);
        $contextNode = collect($diagram['nodes'])->firstWhere('type', C4ElementTypes::CONTEXT);

        $this->actingAs($this->user)
            ->postJson(route('c4.relationships.store'), [
                'source_id' => $userNode['id'],
                'target_id' => $contextNode['id'],
                'source_type' => C4ElementTypes::USER,
                'target_type' => C4ElementTypes::CONTEXT,
                'protocol' => 'HTTP',
                'system_id' => $system->id,
            ])
            ->assertCreated();

        $this->assertDatabaseHas('c4_relationships', [
            'source_id' => $userNode['id'],
            'target_id' => $contextNode['id'],
            'source_type' => C4ElementTypes::USER,
            'target_type' => C4ElementTypes::CONTEXT,
        ]);
    }

    public function test_context_relationship_resolves_legacy_diagram_ids(): void
    {
        $domain = Domain::create(['name' => 'Legacy Rel Domain', 'slug' => 'legacy-rel-domain']);
        $system = System::create(['name' => 'Legacy Rel System', 'domain_id' => $domain->id]);
        $context = app(C4SyncService::class)->enableC4ForSystem($system);
        $context->update([
            'users' => [['id' => 'user-default', 'name' => 'End User', 'role' => 'User']],
        ]);

        $response = $this->actingAs($this->user)
            ->postJson(route('c4.relationships.store'), [
                'source_id' => 'user-default',
                'target_id' => 'system-'.$system->id,
                'source_type' => C4ElementTypes::USER,
                'target_type' => C4ElementTypes::CONTEXT,
                'protocol' => 'HTTP',
                'system_id' => $system->id,
            ]);

        $response->assertCreated();

        $resolvedUserId = $context->fresh()->users[0]['id'];
        $this->assertTrue(Str::isUuid($resolvedUserId));
        $this->assertDatabaseHas('c4_relationships', [
            'source_id' => $resolvedUserId,
            'target_id' => $context->id,
        ]);
    }
}
