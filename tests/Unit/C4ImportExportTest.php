<?php

namespace Tests\Unit;

use App\Models\C4Import;
use App\Models\Domain;
use App\Models\System;
use App\Models\User;
use App\Services\C4ExportService;
use App\Services\C4Import\C4OpenApiImportService;
use App\Services\C4Import\C4StructurizrImportService;
use App\Support\C4ImportTypes;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class C4ImportExportTest extends TestCase
{
    use RefreshDatabase;

    private System $system;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $domain = Domain::create(['name' => 'Import Domain', 'slug' => 'import-domain']);
        $this->system = System::create(['name' => 'Import System', 'domain_id' => $domain->id]);
        $this->user = User::factory()->create();
    }

    public function test_openapi_import_creates_containers_and_components(): void
    {
        $spec = json_encode([
            'openapi' => '3.0.0',
            'info' => ['title' => 'Pet Store', 'version' => '1.0.0'],
            'paths' => [
                '/pets' => [
                    'get' => ['operationId' => 'listPets', 'summary' => 'List pets'],
                    'post' => ['operationId' => 'createPet', 'summary' => 'Create pet'],
                ],
            ],
        ]);

        $import = C4Import::create([
            'system_id' => $this->system->id,
            'user_id' => $this->user->id,
            'type' => C4ImportTypes::OPENAPI,
            'status' => C4Import::STATUS_PENDING,
            'file_path' => 'test/openapi.json',
            'original_filename' => 'openapi.json',
        ]);

        $result = app(C4OpenApiImportService::class)->import($this->system, $spec, $import);

        $this->system->refresh();
        $this->assertTrue($this->system->c4_enabled);
        $this->assertEquals(2, $result['components']);
        $this->assertGreaterThanOrEqual(2, $this->system->c4Containers()->count());
    }

    public function test_structurizr_import_parses_dsl(): void
    {
        $dsl = <<<'DSL'
workspace {
    model {
        user = person "Customer" "A customer"
        api = softwareSystem "API" "Core API"
        db = softwareSystem "Legacy DB" "External datastore" "External"
        web = container "Web App" "SPA" "React"
        apiService = container "API Service" "REST" "Laravel"
        user -> web "Uses"
        web -> apiService "HTTPS"
    }
}
DSL;

        $import = C4Import::create([
            'system_id' => $this->system->id,
            'user_id' => $this->user->id,
            'type' => C4ImportTypes::STRUCTURIZR,
            'status' => C4Import::STATUS_PENDING,
            'file_path' => 'test/model.dsl',
            'original_filename' => 'model.dsl',
        ]);

        $result = app(C4StructurizrImportService::class)->import($this->system, $dsl, $import);

        $this->system->refresh();
        $this->assertTrue($this->system->c4_enabled);
        $this->assertGreaterThanOrEqual(2, $result['containers']);
        $this->assertEquals('API', $this->system->c4Context?->name);
    }

    public function test_drawio_export_produces_valid_xml(): void
    {
        app(C4OpenApiImportService::class)->import(
            $this->system,
            json_encode([
                'openapi' => '3.0.0',
                'info' => ['title' => 'Test', 'version' => '1.0'],
                'paths' => ['/health' => ['get' => ['operationId' => 'healthCheck']]],
            ]),
            C4Import::create([
                'system_id' => $this->system->id,
                'user_id' => $this->user->id,
                'type' => C4ImportTypes::OPENAPI,
                'status' => C4Import::STATUS_PENDING,
                'file_path' => 'x',
                'original_filename' => 'x.json',
            ]),
        );

        $xml = app(C4ExportService::class)->toDrawIoXml($this->system->fresh());

        $this->assertStringContainsString('<mxfile', $xml);
        $this->assertStringContainsString('<mxGraphModel', $xml);
        $this->assertStringContainsString('API Gateway', $xml);
    }
}
