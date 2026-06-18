<?php

namespace Tests\Feature;

use App\Models\C4Container;
use App\Models\C4Context;
use App\Models\Domain;
use App\Models\System;
use App\Models\User;
use App\Support\C4ChangeRequestStatuses;
use App\Support\C4ContainerTypes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class C4CollaborationTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private System $system;

    private C4Container $container;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $domain = Domain::create(['name' => 'Collab Domain', 'slug' => 'collab-domain']);
        $this->system = System::create(['name' => 'Collab System', 'domain_id' => $domain->id]);
        $context = C4Context::create(['name' => 'Context', 'description' => 'Test']);
        $this->system->update(['c4_enabled' => true, 'c4_context_id' => $context->id]);
        $this->container = C4Container::create([
            'system_id' => $this->system->id,
            'name' => 'API',
            'type' => C4ContainerTypes::BACKEND,
        ]);
    }

    public function test_user_can_post_and_list_comments(): void
    {
        $this->actingAs($this->user)
            ->postJson(route('c4.comments.store'), [
                'element_type' => 'container',
                'element_id' => $this->container->id,
                'body' => 'Needs review for production.',
            ])
            ->assertCreated();

        $this->actingAs($this->user)
            ->getJson(route('c4.comments.index', [
                'element_type' => 'container',
                'element_id' => $this->container->id,
            ]))
            ->assertOk()
            ->assertJsonPath('comments.0.body', 'Needs review for production.');
    }

    public function test_change_request_approval_workflow(): void
    {
        $create = $this->actingAs($this->user)
            ->postJson(route('c4.systems.change-requests.store', $this->system), [
                'title' => 'Add cache layer',
                'description' => 'Introduce Redis cache',
                'impact' => 'Improved read performance',
                'submit' => true,
            ]);

        $create->assertCreated();
        $id = $create->json('id');

        $reviewer = User::factory()->create();

        $this->actingAs($reviewer)
            ->postJson(route('c4.change-requests.review', $id), [
                'action' => 'approve',
            ])
            ->assertOk()
            ->assertJsonPath('status', C4ChangeRequestStatuses::APPROVED);
    }

    public function test_tech_radar_page_loads(): void
    {
        $this->actingAs($this->user)
            ->get(route('c4.tech-radar.index'))
            ->assertOk()
            ->assertSee('Technology Radar');
    }

    public function test_adr_crud(): void
    {
        $this->actingAs($this->user)
            ->get(route('c4.adrs.index'))
            ->assertOk()
            ->assertSee('Architectural Decision Records');

        $this->actingAs($this->user)
            ->post(route('c4.adrs.store'), [
                'title' => 'Choose message broker',
                'status' => 'proposed',
                'system_id' => $this->system->id,
                'context' => 'Event-driven integrations growing',
                'decision' => 'Evaluate Kafka vs RabbitMQ',
                'consequences' => 'Ops overhead for broker cluster',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('architectural_decision_records', [
            'title' => 'Choose message broker',
            'system_id' => $this->system->id,
        ]);
    }
}
