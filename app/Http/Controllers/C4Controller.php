<?php

namespace App\Http\Controllers;

use App\Http\Resources\C4ComponentResource;
use App\Http\Resources\C4ContainerResource;
use App\Http\Resources\C4ContextResource;
use App\Http\Resources\C4RelationshipResource;
use App\Models\C4Component;
use App\Models\C4Container;
use App\Jobs\ProcessC4ImportJob;
use App\Models\C4Import;
use App\Models\C4ModelVersion;
use App\Models\C4Relationship;
use App\Models\C4ShareLink;
use App\Models\Domain;
use App\Models\System;
use App\Models\User;
use App\Services\C4ContextElementService;
use App\Services\C4DiagramService;
use App\Services\C4ExportService;
use App\Services\C4RelationshipValidator;
use App\Services\C4SyncService;
use App\Services\C4Import\C4ImportService;
use App\Services\C4VersionService;
use App\Support\C4ComponentTypes;
use App\Support\C4ContainerTypes;
use App\Support\C4ElementTypes;
use App\Support\C4ImportTypes;
use App\Support\C4Protocols;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\View\View;
use RuntimeException;

class C4Controller extends Controller
{
    public function __construct(
        private C4DiagramService $diagramService,
        private C4SyncService $syncService,
        private C4ExportService $exportService,
        private C4VersionService $versionService,
        private C4RelationshipValidator $relationshipValidator,
        private C4ImportService $importService,
        private C4ContextElementService $contextElementService,
    ) {}

    public function index(Request $request): View
    {
        $systems = System::query()
            ->with(['domain', 'vendor', 'c4Context'])
            ->when($request->integer('domain_id'), fn ($q, $id) => $q->where('domain_id', $id))
            ->when($request->boolean('c4_only'), fn ($q) => $q->where('c4_enabled', true))
            ->orderBy('name')
            ->get();

        return view('c4.index', [
            'systems' => $systems,
            'domains' => Domain::orderBy('name')->get(),
            'filters' => [
                'domain_id' => $request->integer('domain_id') ?: null,
                'c4_only' => $request->boolean('c4_only'),
            ],
        ]);
    }

    public function context(System $system): View
    {
        $this->ensureC4Enabled($system);

        return view('c4.show', $this->diagramViewData(
            $system->load(['c4Context', 'domain', 'vendor']),
            'context',
            $this->diagramService->buildContextDiagram($system),
        ));
    }

    public function containers(System $system): View
    {
        $this->ensureC4Enabled($system);

        return view('c4.show', $this->diagramViewData(
            $system->load(['c4Containers.components', 'domain', 'vendor']),
            'container',
            $this->diagramService->buildContainerDiagram($system),
        ));
    }

    public function showContainer(C4Container $container): View
    {
        $system = $container->system;
        $this->ensureC4Enabled($system);

        return view('c4.show', array_merge(
            $this->diagramViewData($system->load(['domain', 'vendor']), 'component', $this->diagramService->buildComponentDiagram($container)),
            ['container' => $container->load('components')],
        ));
    }

    /**
     * @return array<string, mixed>
     */
    private function diagramViewData(System $system, string $level, array $diagramData): array
    {
        return [
            'system' => $system,
            'level' => $level,
            'diagramData' => $diagramData,
            'containerTypes' => C4ContainerTypes::ALL,
            'componentTypes' => C4ComponentTypes::ALL,
            'protocols' => C4Protocols::ALL,
            'users' => User::orderBy('name')->get(['id', 'name', 'email']),
            'contextId' => $system->c4_context_id,
        ];
    }

    public function diagramData(Request $request): JsonResponse
    {
        $level = $request->input('level', 'context');

        if ($level === 'component' && $request->filled('container_id')) {
            $container = C4Container::with('system')->findOrFail($request->input('container_id'));

            return response()->json($this->diagramService->buildComponentDiagram($container));
        }

        $system = System::with(['c4Context', 'c4Containers.components'])->findOrFail($request->integer('system_id'));

        return response()->json(match ($level) {
            'container' => $this->diagramService->buildContainerDiagram($system),
            default => $this->diagramService->buildContextDiagram($system),
        });
    }

    public function enable(System $system): RedirectResponse
    {
        $this->syncService->enableC4ForSystem($system);

        return redirect()
            ->route('c4.systems.context', $system)
            ->with('success', 'C4 model enabled for '.$system->name);
    }

    public function sync(System $system): RedirectResponse
    {
        $this->syncService->syncFromApis($system);

        return back()->with('success', 'C4 model synced from API documentation.');
    }

    public function updateContext(Request $request, System $system): JsonResponse
    {
        $context = $system->c4Context;
        abort_unless($context, 404, 'C4 context not found.');

        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'external_systems' => 'nullable|array',
            'users' => 'nullable|array',
            'position' => 'nullable|array',
            'metadata' => 'nullable|array',
        ]);

        $context->update($data);
        $this->versionService->snapshot($system, $request->input('commit_message', 'Updated context diagram'));

        return response()->json(new C4ContextResource($context->fresh()));
    }

    public function storeContainer(Request $request, System $system): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => C4ContainerTypes::validationRule(),
            'technology' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'position' => 'nullable|array',
            'metadata' => 'nullable|array',
        ]);

        $container = $system->c4Containers()->create($data);
        $this->versionService->snapshot($system, $request->input('commit_message', 'Added container: '.$data['name']));

        return response()->json(new C4ContainerResource($container), 201);
    }

    public function updateContainer(Request $request, C4Container $container): JsonResponse
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'type' => 'sometimes|'.C4ContainerTypes::validationRule(),
            'technology' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'position' => 'nullable|array',
            'metadata' => 'nullable|array',
            'sunset_date' => 'nullable|date',
        ]);

        $container->update($data);
        $this->versionService->snapshot($container->system, $request->input('commit_message', 'Updated container: '.$container->name));

        return response()->json(new C4ContainerResource($container->fresh()));
    }

    public function destroyContainer(C4Container $container): JsonResponse
    {
        $system = $container->system;
        $name = $container->name;
        $container->delete();
        $this->versionService->snapshot($system, 'Removed container: '.$name);

        return response()->json(['message' => 'Container deleted.']);
    }

    public function storeComponent(Request $request, C4Container $container): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => C4ComponentTypes::validationRule(),
            'technology' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'dependencies' => 'nullable|array',
            'position' => 'nullable|array',
            'metadata' => 'nullable|array',
        ]);

        $component = $container->components()->create($data);
        $this->versionService->snapshot($container->system, $request->input('commit_message', 'Added component: '.$data['name']));

        return response()->json(new C4ComponentResource($component), 201);
    }

    public function updateComponent(Request $request, C4Component $component): JsonResponse
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'type' => 'sometimes|'.C4ComponentTypes::validationRule(),
            'technology' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'dependencies' => 'nullable|array',
            'position' => 'nullable|array',
            'metadata' => 'nullable|array',
            'sunset_date' => 'nullable|date',
        ]);

        $component->update($data);
        $this->versionService->snapshot($component->container->system, $request->input('commit_message', 'Updated component: '.$component->name));

        return response()->json(new C4ComponentResource($component->fresh()));
    }

    public function destroyComponent(C4Component $component): JsonResponse
    {
        $system = $component->container->system;
        $name = $component->name;
        $component->delete();
        $this->versionService->snapshot($system, 'Removed component: '.$name);

        return response()->json(['message' => 'Component deleted.']);
    }

    public function storeRelationship(Request $request): JsonResponse
    {
        $system = System::with(['c4Context', 'c4Containers'])->findOrFail($request->input('system_id'));

        $request->merge([
            'source_id' => $this->contextElementService->resolveRelationshipId(
                $system,
                (string) $request->input('source_id'),
                'source_id',
            ),
            'target_id' => $this->contextElementService->resolveRelationshipId(
                $system,
                (string) $request->input('target_id'),
                'target_id',
            ),
        ]);

        $data = $request->validate([
            'source_id' => 'required|uuid',
            'target_id' => 'required|uuid',
            'source_type' => C4ElementTypes::relationshipValidationRule(),
            'target_type' => C4ElementTypes::relationshipValidationRule(),
            'protocol' => 'nullable|'.C4Protocols::validationRule(),
            'description' => 'nullable|string',
            'sync' => 'boolean',
            'metadata' => 'nullable|array',
            'system_id' => 'required|integer|exists:systems,id',
        ]);

        try {
            $this->relationshipValidator->validateNoCycle($data['source_id'], $data['target_id']);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        $relationship = C4Relationship::create($data);
        $this->versionService->snapshot($system, $request->input('commit_message', 'Added relationship'));

        return response()->json(new C4RelationshipResource($relationship), 201);
    }

    public function updateRelationship(Request $request, C4Relationship $relationship): JsonResponse
    {
        $data = $request->validate([
            'protocol' => 'nullable|'.C4Protocols::validationRule(),
            'description' => 'nullable|string',
            'sync' => 'boolean',
            'metadata' => 'nullable|array',
            'system_id' => 'required|integer|exists:systems,id',
        ]);

        $relationship->update($data);

        return response()->json(new C4RelationshipResource($relationship->fresh()));
    }

    public function destroyRelationship(C4Relationship $relationship): JsonResponse
    {
        $relationship->delete();

        return response()->json(['message' => 'Relationship deleted.']);
    }

    public function search(Request $request, System $system): JsonResponse
    {
        $query = $request->input('q', '');

        return response()->json([
            'results' => $this->diagramService->search($system, $query),
        ]);
    }

    public function versions(System $system): JsonResponse
    {
        $versions = $system->c4ModelVersions()
            ->with('user')
            ->orderByDesc('version_number')
            ->limit(50)
            ->get();

        return response()->json($versions);
    }

    public function rollback(Request $request, System $system, C4ModelVersion $version): JsonResponse
    {
        abort_unless($version->system_id === $system->id, 404);
        $this->versionService->rollback($system, $version);
        $this->versionService->snapshot($system, 'Rollback to version '.$version->version_number);

        return response()->json(['message' => 'Rolled back to version '.$version->version_number]);
    }

    public function export(Request $request, System $system): Response|JsonResponse
    {
        $format = $request->input('format', 'json');

        return match ($format) {
            'structurizr' => response($this->exportService->toStructurizrDsl($system), 200, [
                'Content-Type' => 'text/plain',
                'Content-Disposition' => 'attachment; filename="'.$system->name.'-c4.dsl"',
            ]),
            'plantuml' => response($this->exportService->toPlantUml($system), 200, [
                'Content-Type' => 'text/plain',
                'Content-Disposition' => 'attachment; filename="'.$system->name.'-c4.puml"',
            ]),
            'drawio' => response($this->exportService->toDrawIoXml($system), 200, [
                'Content-Type' => 'application/xml',
                'Content-Disposition' => 'attachment; filename="'.$system->name.'-c4.drawio"',
            ]),
            default => response()->json($this->exportService->toJson($system)),
        };
    }

    public function createShareLink(Request $request, System $system): JsonResponse
    {
        $data = $request->validate([
            'level' => 'required|in:context,container,component',
            'expires_at' => 'nullable|date|after:now',
            'password' => 'nullable|string|min:6',
        ]);

        $link = C4ShareLink::create([
            'system_id' => $system->id,
            'created_by' => $request->user()->id,
            'token' => Str::random(48),
            'password' => isset($data['password']) ? bcrypt($data['password']) : null,
            'expires_at' => $data['expires_at'] ?? null,
            'level' => $data['level'],
        ]);

        return response()->json([
            'url' => url('/c4/shared/'.$link->token),
            'expires_at' => $link->expires_at?->toIso8601String(),
        ], 201);
    }

    public function import(Request $request, System $system): JsonResponse
    {
        $type = $request->input('import_type');
        $request->validate([
            'import_type' => C4ImportTypes::validationRule(),
            'file' => 'required|file|max:10240',
        ]);

        $extension = strtolower($request->file('file')->getClientOriginalExtension());
        $allowed = C4ImportTypes::acceptedExtensions($type);
        if (! in_array($extension, $allowed, true)) {
            return response()->json([
                'message' => 'Invalid file type. Accepted: '.implode(', ', $allowed),
            ], 422);
        }

        $storedPath = $request->file('file')->store('c4-imports/'.$system->id, 'local');

        $import = $this->importService->createImport(
            $system,
            $request->user()->id,
            $type,
            $storedPath,
            $request->file('file')->getClientOriginalName(),
            ['base_url' => $request->input('base_url')],
        );

        ProcessC4ImportJob::dispatch($import);

        return response()->json([
            'import_id' => $import->id,
            'status' => $import->status,
            'message' => 'Import queued. Poll status for progress.',
            'status_url' => route('c4.imports.status', $import),
        ], 202);
    }

    public function importStatus(C4Import $import): JsonResponse
    {
        abort_unless($import->user_id === auth()->id(), 403);

        return response()->json([
            'id' => $import->id,
            'type' => $import->type,
            'type_label' => C4ImportTypes::label($import->type),
            'status' => $import->status,
            'progress' => $import->progress,
            'result' => $import->result,
            'error_message' => $import->error_message,
            'original_filename' => $import->original_filename,
            'started_at' => $import->started_at?->toIso8601String(),
            'completed_at' => $import->completed_at?->toIso8601String(),
            'is_finished' => $import->isFinished(),
            'redirect_url' => $import->status === C4Import::STATUS_COMPLETED
                ? route('c4.systems.containers', $import->system_id)
                : null,
        ]);
    }

    public function sharedView(string $token): View
    {
        $link = C4ShareLink::where('token', $token)->firstOrFail();
        abort_unless($link->isValid(), 403, 'This share link has expired.');

        $system = $link->system()->with(['c4Context', 'c4Containers.components'])->firstOrFail();

        $diagramData = match ($link->level) {
            'container' => $this->diagramService->buildContainerDiagram($system),
            'component' => $this->diagramService->buildContainerDiagram($system),
            default => $this->diagramService->buildContextDiagram($system),
        };

        return view('c4.shared', [
            'system' => $system,
            'level' => $link->level,
            'diagramData' => $diagramData,
            'readOnly' => true,
        ]);
    }

    private function ensureC4Enabled(System $system): void
    {
        if (! $system->c4_enabled) {
            $this->syncService->enableC4ForSystem($system);
            $system->refresh();
        }
    }
}
