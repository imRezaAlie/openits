<?php

namespace App\Http\Controllers;

use App\Models\Api;
use App\Models\Bpmn;
use App\Models\Domain;
use App\Models\System;
use App\Models\Vendor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SystemController extends Controller
{
    public function index(Request $request): View
    {
        $query = System::with(['parent', 'vendor', 'domain'])
            ->withCount(['ownedApis', 'bpmns', 'technologies', 'servers', 'documents', 'children']);

        if ($vendorId = $request->integer('vendor_id')) {
            $query->where('vendor_id', $vendorId);
        }

        if ($domainId = $request->integer('domain_id')) {
            $query->where('domain_id', $domainId);
        }

        if ($search = trim((string) $request->get('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('system_type', 'like', "%{$search}%");
            });
        }

        $perPage = in_array($request->integer('per_page'), [15, 30, 50, 100], true)
            ? $request->integer('per_page')
            : 30;

        $systemsTotal = System::count();
        $viewMode = in_array($request->get('view'), ['grid', 'table'], true)
            ? $request->get('view')
            : ($systemsTotal > 12 ? 'table' : 'grid');

        $systems = $query->orderBy('name')->paginate($perPage)->withQueryString();
        $vendors = Vendor::withCount('systems')->orderBy('name')->get();
        $domains = Domain::withCount('systems')->orderBy('name')->get();
        $allSystemsForSelect = System::with(['vendor', 'domain'])->orderBy('name')->get(['id', 'name', 'vendor_id', 'domain_id', 'parent_system_id']);

        $stats = [
            'systems_filtered' => $systems->total(),
            'systems_total' => $systemsTotal,
            'apis_total' => Api::whereNotNull('owner_system_id')->count(),
            'processes_total' => Bpmn::whereNotNull('system_id')->count(),
            'technologies_total' => DB::table('system_technology')->count(),
        ];

        return view('systems.index', compact(
            'systems',
            'vendors',
            'domains',
            'stats',
            'viewMode',
            'perPage',
            'allSystemsForSelect',
        ));
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'vendor_id' => 'nullable|exists:vendors,id',
            'domain_id' => 'required|exists:domains,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'system_type' => 'nullable|string|max:100',
            'icon' => 'nullable|string|max:255',
            'parent_system_id' => 'nullable|exists:systems,id',
        ]);

        if (! empty($validated['parent_system_id'])) {
            $parent = System::find($validated['parent_system_id']);
            if ($parent?->domain_id && (int) $parent->domain_id !== (int) $validated['domain_id']) {
                return back()->withErrors(['domain_id' => 'Child systems must belong to the same domain as their parent.']);
            }
        }

        $system = System::create($validated);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'System created successfully.', 'system' => $system]);
        }

        return redirect()->route('systems.index')->with('success', 'System created successfully.');
    }

    public function update(Request $request, System $system): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'vendor_id' => 'nullable|exists:vendors,id',
            'domain_id' => 'required|exists:domains,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'system_type' => 'nullable|string|max:100',
            'icon' => 'nullable|string|max:255',
            'parent_system_id' => 'nullable|exists:systems,id',
        ]);

        if (isset($validated['parent_system_id']) && (int) $validated['parent_system_id'] === $system->id) {
            return back()->withErrors(['parent_system_id' => 'A system cannot be its own parent.']);
        }

        if (! empty($validated['parent_system_id'])) {
            $parent = System::find($validated['parent_system_id']);
            if ($parent?->domain_id && (int) $parent->domain_id !== (int) $validated['domain_id']) {
                return back()->withErrors(['domain_id' => 'Child systems must belong to the same domain as their parent.']);
            }
        }

        if ($system->children()->exists() && (int) $validated['domain_id'] !== (int) $system->domain_id) {
            $childDomainMismatch = $system->children()->where('domain_id', '!=', $validated['domain_id'])->exists();
            if ($childDomainMismatch) {
                return back()->withErrors(['domain_id' => 'Update child systems to the same domain before changing this system\'s domain.']);
            }
        }

        $system->update($validated);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'System updated successfully.', 'system' => $system]);
        }

        return redirect()->route('systems.index')->with('success', 'System updated successfully.');
    }

    public function destroy(System $system): RedirectResponse
    {
        if ($system->children()->exists()) {
            return redirect()
                ->route('systems.index')
                ->with('error', 'Cannot delete a system that has child systems. Remove or reassign them first.');
        }

        if ($system->ownedApis()->exists()) {
            return redirect()
                ->route('systems.index')
                ->with('error', 'Cannot delete a system that owns APIs. Reassign API ownership first.');
        }

        $system->delete();

        return redirect()->route('systems.index')->with('success', 'System deleted successfully.');
    }
}
