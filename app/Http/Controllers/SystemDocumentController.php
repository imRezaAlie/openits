<?php

namespace App\Http\Controllers;

use App\Models\System;
use App\Models\SystemDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SystemDocumentController extends Controller
{
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

        return view('systems.documents', compact('system'));
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
