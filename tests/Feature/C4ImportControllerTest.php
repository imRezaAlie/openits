<?php

namespace Tests\Feature;

use App\Jobs\ProcessC4ImportJob;
use App\Models\C4Import;
use App\Models\Domain;
use App\Models\System;
use App\Models\User;
use App\Support\C4ImportTypes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class C4ImportControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private System $system;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $domain = Domain::create(['name' => 'Queue Domain', 'slug' => 'queue-domain']);
        $this->system = System::create(['name' => 'Queue System', 'domain_id' => $domain->id]);
    }

    public function test_import_queues_job(): void
    {
        Queue::fake();
        Storage::fake('local');

        $file = UploadedFile::fake()->createWithContent('api.json', json_encode([
            'openapi' => '3.0.0',
            'info' => ['title' => 'Test API', 'version' => '1.0'],
            'paths' => [],
        ]));

        $response = $this->actingAs($this->user)->postJson(
            route('c4.systems.import', $this->system),
            ['import_type' => C4ImportTypes::OPENAPI, 'file' => $file],
        );

        $response->assertStatus(202)
            ->assertJsonStructure(['import_id', 'status_url']);

        Queue::assertPushed(ProcessC4ImportJob::class);
    }

    public function test_import_status_endpoint(): void
    {
        $import = C4Import::create([
            'system_id' => $this->system->id,
            'user_id' => $this->user->id,
            'type' => C4ImportTypes::OPENAPI,
            'status' => C4Import::STATUS_COMPLETED,
            'file_path' => 'gone.json',
            'original_filename' => 'api.json',
            'progress' => 100,
            'result' => ['components' => 3],
        ]);

        $this->actingAs($this->user)
            ->getJson(route('c4.imports.status', $import))
            ->assertOk()
            ->assertJson([
                'status' => 'completed',
                'progress' => 100,
                'is_finished' => true,
            ]);
    }

    public function test_drawio_export_download(): void
    {
        $this->actingAs($this->user)
            ->get(route('c4.systems.export', [$this->system, 'format' => 'drawio']))
            ->assertOk()
            ->assertHeader('content-type', 'application/xml');
    }

    public function test_import_processes_openapi_via_job(): void
    {
        Storage::fake('local');

        $content = json_encode([
            'openapi' => '3.0.0',
            'info' => ['title' => 'Job API', 'version' => '1.0'],
            'paths' => [
                '/items' => ['get' => ['operationId' => 'listItems']],
            ],
        ]);

        $path = 'c4-imports/'.$this->system->id.'/test.json';
        Storage::disk('local')->put($path, $content);

        $import = C4Import::create([
            'system_id' => $this->system->id,
            'user_id' => $this->user->id,
            'type' => C4ImportTypes::OPENAPI,
            'status' => C4Import::STATUS_PENDING,
            'file_path' => $path,
            'original_filename' => 'test.json',
        ]);

        ProcessC4ImportJob::dispatchSync($import);

        $import->refresh();
        $this->assertEquals(C4Import::STATUS_COMPLETED, $import->status);
        $this->assertTrue($this->system->fresh()->c4_enabled);
    }
}
