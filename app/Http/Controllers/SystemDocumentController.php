<?php

namespace App\Http\Controllers;

use App\Models\System;
use App\Models\SystemDocument;
use App\Services\SystemMarkdownGenerator;
use App\Support\SystemDocumentTypes;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SystemDocumentController extends Controller
{
    public function __construct(
        private SystemMarkdownGenerator $markdownGenerator,
    ) {}
    public function catalog(Request $request): View
    {
        $query = SystemDocument::with(['system.vendor'])
            ->orderBy('name')
            ->orderByDesc('version');

        if ($systemId = $request->integer('system_id')) {
            $query->where('system_id', $systemId);
        }

        if ($search = trim((string) $request->get('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('version', 'like', "%{$search}%")
                    ->orWhere('attachment_original_name', 'like', "%{$search}%");
            });
        }

        $documents = $query->get();
        $systems = System::with('vendor')->orderBy('name')->get(['id', 'name', 'vendor_id']);

        $stats = [
            'total' => SystemDocument::count(),
            'systems_with_docs' => SystemDocument::distinct('system_id')->count('system_id'),
        ];

        return view('documents.index', compact('documents', 'systems', 'stats'));
    }

    public function index(System $system): View
    {
        $system->load([
            'vendor',
            'parent',
            'documents' => fn ($q) => $q->orderBy('name')->orderByDesc('version'),
        ]);

        $documentTypes = $this->markdownGenerator->availableTypes();

        return view('systems.documents', compact('system', 'documentTypes'));
    }

    public function preview(System $system, string $type): View
    {
        if (! SystemDocumentTypes::isValid($type)) {
            abort(404);
        }

        $markdown = $this->markdownGenerator->generate($system, $type);
        $typeLabel = SystemDocumentTypes::label($type);

        return view('systems.document-markdown', compact('system', 'type', 'typeLabel', 'markdown'));
    }

    public function generate(Request $request, System $system): RedirectResponse
    {
        $types = array_keys(SystemDocumentTypes::all());

        $validated = $request->validate([
            'types' => 'required|array|min:1',
            'types.*' => 'string|in:'.implode(',', $types),
            'version' => 'nullable|string|max:50',
        ]);

        $count = 0;

        foreach ($validated['types'] as $type) {
            $this->markdownGenerator->persist($system, $type, $validated['version'] ?? null);
            $count++;
        }

        return redirect()
            ->route('systems.documents', $system)
            ->with('success', "{$count} markdown document(s) generated successfully.");
    }

    public function view(System $system, SystemDocument $systemDocument): View
    {
        $this->ensureDocumentBelongsToSystem($system, $systemDocument);

        if (! $systemDocument->isMarkdown()) {
            abort(404, 'Only markdown documents can be previewed.');
        }

        $markdown = $systemDocument->readContent();

        if ($markdown === null) {
            abort(404, 'Document content not found.');
        }

        $typeLabel = $systemDocument->name;

        return view('systems.document-markdown', [
            'system' => $system,
            'type' => null,
            'typeLabel' => $typeLabel,
            'markdown' => $markdown,
            'systemDocument' => $systemDocument,
        ]);
    }

    public function createMarkdown(System $system): View
    {
        $system->load('vendor');

        return view('systems.document-editor', [
            'system' => $system,
            'systemDocument' => null,
            'content' => old('content', "# New Documentation\n\nStart writing here...\n"),
        ]);
    }

    public function editMarkdown(System $system, SystemDocument $systemDocument): View
    {
        $this->ensureDocumentBelongsToSystem($system, $systemDocument);

        if (! $systemDocument->isMarkdown()) {
            abort(404, 'Only markdown documents can be edited live.');
        }

        $system->load('vendor');
        $content = $systemDocument->readContent() ?? '';

        return view('systems.document-editor', [
            'system' => $system,
            'systemDocument' => $systemDocument,
            'content' => old('content', $content),
        ]);
    }

    public function storeMarkdown(Request $request, System $system): RedirectResponse
    {
        $validated = $this->validateMarkdown($request);

        $filename = $this->uniqueMarkdownFilename($system, $validated['name']);
        $path = "system-documents/{$system->id}/{$filename}";

        Storage::disk('local')->put($path, $validated['content']);

        $system->documents()->create([
            'name' => $validated['name'],
            'version' => $validated['version'] ?? null,
            'attachment_path' => $path,
            'attachment_original_name' => $filename,
        ]);

        return redirect()
            ->route('systems.documents', $system)
            ->with('success', 'Markdown document saved successfully.');
    }

    public function updateMarkdown(Request $request, System $system, SystemDocument $systemDocument): RedirectResponse
    {
        $this->ensureDocumentBelongsToSystem($system, $systemDocument);

        if (! $systemDocument->isMarkdown()) {
            abort(404, 'Only markdown documents can be edited live.');
        }

        $validated = $this->validateMarkdown($request);

        $systemDocument->update([
            'name' => $validated['name'],
            'version' => $validated['version'] ?? null,
        ]);

        $systemDocument->writeContent($validated['content']);

        return redirect()
            ->route('systems.documents.view', [$system, $systemDocument])
            ->with('success', 'Markdown document updated successfully.');
    }

    public function store(Request $request, System $system): RedirectResponse
    {
        $validated = $this->validateDocument($request, true);
        $file = $request->file('attachment');

        $path = $file->store("system-documents/{$system->id}", 'local');

        $system->documents()->create([
            'name' => $validated['name'],
            'version' => $validated['version'] ?? null,
            'attachment_path' => $path,
            'attachment_original_name' => $file->getClientOriginalName(),
        ]);

        return redirect()
            ->route('systems.documents', $system)
            ->with('success', 'Document added successfully.');
    }

    public function update(Request $request, System $system, SystemDocument $systemDocument): RedirectResponse
    {
        $this->ensureDocumentBelongsToSystem($system, $systemDocument);

        $validated = $this->validateDocument($request, false);

        $data = [
            'name' => $validated['name'],
            'version' => $validated['version'] ?? null,
        ];

        if ($request->hasFile('attachment')) {
            $systemDocument->deleteAttachment();
            $file = $request->file('attachment');
            $data['attachment_path'] = $file->store("system-documents/{$system->id}", 'local');
            $data['attachment_original_name'] = $file->getClientOriginalName();
        }

        $systemDocument->update($data);

        return redirect()
            ->route('systems.documents', $system)
            ->with('success', 'Document updated successfully.');
    }

    public function destroy(System $system, SystemDocument $systemDocument): RedirectResponse
    {
        $this->ensureDocumentBelongsToSystem($system, $systemDocument);

        $systemDocument->delete();

        return redirect()
            ->route('systems.documents', $system)
            ->with('success', 'Document removed successfully.');
    }

    public function download(System $system, SystemDocument $systemDocument): StreamedResponse
    {
        $this->ensureDocumentBelongsToSystem($system, $systemDocument);

        if (! Storage::disk('local')->exists($systemDocument->attachment_path)) {
            abort(404, 'Attachment not found.');
        }

        return Storage::disk('local')->download(
            $systemDocument->attachment_path,
            $systemDocument->attachment_original_name
        );
    }

    /** @return array<string, mixed> */
    private function validateMarkdown(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'version' => 'nullable|string|max:50',
            'content' => 'required|string|max:500000',
        ]);
    }

    private function uniqueMarkdownFilename(System $system, string $name): string
    {
        $base = Str::slug($name) ?: 'document';
        $filename = $base.'.md';
        $counter = 1;

        while ($system->documents()->where('attachment_original_name', $filename)->exists()) {
            $filename = $base.'-'.$counter.'.md';
            $counter++;
        }

        return $filename;
    }

    /** @return array<string, mixed> */
    private function validateDocument(Request $request, bool $attachmentRequired): array
    {
        $attachmentRule = $attachmentRequired ? 'required' : 'nullable';

        return $request->validate([
            'name' => 'required|string|max:255',
            'version' => 'nullable|string|max:50',
            'attachment' => "{$attachmentRule}|file|max:20480",
        ]);
    }

    private function ensureDocumentBelongsToSystem(System $system, SystemDocument $document): void
    {
        if ((int) $document->system_id !== (int) $system->id) {
            abort(404);
        }
    }
}
