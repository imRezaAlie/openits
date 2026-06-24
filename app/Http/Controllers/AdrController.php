<?php

namespace App\Http\Controllers;

use App\Models\ArchitecturalDecisionRecord;
use App\Models\System;
use App\Models\User;
use App\Support\AdrStatuses;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdrController extends Controller
{
    public function index(Request $request): View
    {
        $query = ArchitecturalDecisionRecord::query()
            ->with(['system', 'author'])
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->integer('system_id')) {
            $query->where('system_id', $request->integer('system_id'));
        }

        return view('c4.adrs.index', [
            'adrs' => $query->paginate(20)->withQueryString(),
            'statuses' => AdrStatuses::ALL,
            'systems' => System::orderBy('name')->get(),
            'filters' => [
                'status' => $request->input('status'),
                'system_id' => $request->integer('system_id') ?: null,
            ],
        ]);
    }

    public function timeline(): View
    {
        $adrs = ArchitecturalDecisionRecord::query()
            ->with(['system', 'author'])
            ->whereNotNull('decided_at')
            ->orderByDesc('decided_at')
            ->get();

        return view('c4.adrs.timeline', compact('adrs'));
    }

    public function create(Request $request): View
    {
        return view('c4.adrs.form', [
            'adr' => new ArchitecturalDecisionRecord(['status' => AdrStatuses::PROPOSED]),
            'systems' => System::orderBy('name')->get(),
            'users' => User::orderBy('name')->get(),
            'statuses' => AdrStatuses::ALL,
            'c4Elements' => $this->c4ElementsForSystem($request->integer('system_id')),
            'selectedSystemId' => $request->integer('system_id') ?: null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateAdr($request);
        $data['author_id'] = $request->user()->id;

        $adr = ArchitecturalDecisionRecord::create($data);
        $adr->syncLinkedElements($this->filterLinkedElements($request));

        return redirect()->route('c4.adrs.show', $adr)->with('success', 'ADR created.');
    }

    public function show(ArchitecturalDecisionRecord $adr): View
    {
        $adr->load(['system', 'author']);

        return view('c4.adrs.show', [
            'adr' => $adr,
            'linkedElements' => $adr->resolvedLinkedElements(),
        ]);
    }

    public function edit(ArchitecturalDecisionRecord $adr): View
    {
        return view('c4.adrs.form', [
            'adr' => $adr,
            'systems' => System::orderBy('name')->get(),
            'users' => User::orderBy('name')->get(),
            'statuses' => AdrStatuses::ALL,
            'c4Elements' => $this->c4ElementsForSystem($adr->system_id),
            'selectedSystemId' => $adr->system_id,
            'linkedElementIds' => collect($adr->resolvedLinkedElements())->pluck('element_id')->all(),
        ]);
    }

    public function update(Request $request, ArchitecturalDecisionRecord $adr): RedirectResponse
    {
        $adr->update($this->validateAdr($request));
        $adr->syncLinkedElements($this->filterLinkedElements($request));

        return redirect()->route('c4.adrs.show', $adr)->with('success', 'ADR updated.');
    }

    public function destroy(ArchitecturalDecisionRecord $adr): RedirectResponse
    {
        $adr->delete();

        return redirect()->route('c4.adrs.index')->with('success', 'ADR deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateAdr(Request $request): array
    {
        $data = $request->validate([
            'system_id' => 'nullable|exists:systems,id',
            'title' => 'required|string|max:255',
            'status' => AdrStatuses::validationRule(),
            'context' => 'nullable|string',
            'decision' => 'nullable|string',
            'consequences' => 'nullable|string',
            'decided_at' => 'nullable|date',
            'reviewers' => 'nullable|array',
            'linked_elements' => 'nullable|array',
            'linked_elements.*.element_id' => 'required|uuid',
            'linked_elements.*.element_type' => 'required|in:container,component,context',
        ]);

        if ($request->filled('reviewer_ids')) {
            $data['reviewers'] = User::whereIn('id', $request->input('reviewer_ids'))->pluck('name', 'id');
        }

        return $data;
    }

    /**
     * @return list<array{id: string, type: string, name: string}>
     */
    private function c4ElementsForSystem(?int $systemId): array
    {
        if (! $systemId) {
            return [];
        }

        $system = System::with(['c4Context', 'c4Containers.components'])->find($systemId);
        if (! $system) {
            return [];
        }

        $elements = [];

        if ($system->c4Context) {
            $elements[] = ['id' => $system->c4Context->id, 'type' => 'context', 'name' => $system->c4Context->name];
        }

        foreach ($system->c4Containers as $container) {
            $elements[] = ['id' => $container->id, 'type' => 'container', 'name' => $container->name];
            foreach ($container->components as $component) {
                $elements[] = ['id' => $component->id, 'type' => 'component', 'name' => $component->name];
            }
        }

        return $elements;
    }

    /**
     * @return list<array{element_id: string, element_type: string}>
     */
    private function filterLinkedElements(Request $request): array
    {
        return array_values(array_filter(
            $request->input('linked_elements', []),
            fn ($e) => ! empty($e['element_id'] ?? null),
        ));
    }
}
