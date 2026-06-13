<?php

namespace App\Http\Controllers;

use App\Models\Bpmn;
use App\Models\System;
use App\Support\DiagramTypes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BpmnController extends Controller
{
    public function catalog(): View
    {
        $processes = Bpmn::with(['system.vendor'])
            ->whereNotNull('system_id')
            ->latest('updated_at')
            ->get();

        return view('processes.index', compact('processes'));
    }

    /**
     * List BPMN processes for a system.
     */
    public function index(System $system): View
    {
        $system->load(['vendor', 'parent', 'bpmns']);

        return view('systems.processes', compact('system'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(System $system)
    {
        return view('systems.create-bpmn', compact('system'));
    }

    public function createSequence(System $system): View
    {
        return view('systems.create-sequence', [
            'system' => $system,
            'template' => DiagramTypes::defaultSequenceTemplate(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'diagram_xml' => 'required|string',
            'system_id' => 'required|exists:systems,id',
            'diagram_type' => ['nullable', Rule::in(DiagramTypes::ALL)],
        ]);

        $diagramType = $validated['diagram_type'] ?? DiagramTypes::BPMN;

        $bpmn = Bpmn::create([
            'name' => $validated['name'],
            'diagram_type' => $diagramType,
            'xml' => $validated['diagram_xml'],
            'system_id' => $validated['system_id'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Process saved successfully.',
            'bpmn' => $bpmn,
            'redirect' => $diagramType === DiagramTypes::SEQUENCE
                ? route('systems.sequence.show', $bpmn)
                : route('systems.bpmn.show', $bpmn),
        ]);
    }

    /**
     * Display / edit the specified BPMN process.
     */
    public function show(Bpmn $bpmn)
    {
        $bpmn->load('system');

        if ($bpmn->isSequence()) {
            return view('systems.sequence-show', compact('bpmn'));
        }

        return view('systems.bpmn-show', compact('bpmn'));
    }

    public function showSequence(Bpmn $bpmn): View
    {
        abort_unless($bpmn->isSequence(), 404);

        $bpmn->load('system');

        return view('systems.sequence-show', compact('bpmn'));
    }

    /**
     * Update the specified BPMN process.
     */
    public function update(Request $request, Bpmn $bpmn): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'diagram_xml' => 'required|string',
        ]);

        $bpmn->update([
            'name' => $validated['name'] ?? $bpmn->name,
            'xml' => $validated['diagram_xml'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Process updated successfully.',
            'bpmn' => $bpmn->fresh(),
        ]);
    }

    /**
     * Remove the specified BPMN process.
     */
    public function destroy(Bpmn $bpmn): JsonResponse
    {
        $bpmn->delete();

        return response()->json([
            'success' => true,
            'message' => 'Process deleted successfully.',
        ]);
    }
}
