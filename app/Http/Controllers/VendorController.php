<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class VendorController extends Controller
{
    public function index(): View
    {
        $vendors = Vendor::withCount(['systems', 'projects'])
            ->orderBy('name')
            ->get();

        return view('vendors.index', compact('vendors'));
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('supplier.index');
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:vendors,name',
        ]);

        Vendor::create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Vendor added successfully!',
            ]);
        }

        return redirect()
            ->route('supplier.index')
            ->with('success', 'Vendor created successfully.');
    }

    public function show(Vendor $supplier): View
    {
        $supplier->loadCount(['systems', 'projects']);
        $supplier->load([
            'systems' => fn ($q) => $q->orderBy('name'),
            'projects' => fn ($q) => $q->orderBy('name'),
        ]);

        return view('vendors.view', ['vendor' => $supplier]);
    }

    public function edit(Vendor $supplier): View
    {
        return view('vendors.edit', ['vendor' => $supplier]);
    }

    public function update(Request $request, Vendor $supplier): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('vendors', 'name')->ignore($supplier->id)],
        ]);

        $supplier->update($validated);

        return redirect()
            ->route('supplier.index')
            ->with('success', 'Vendor updated successfully.');
    }

    public function destroy(Vendor $supplier): RedirectResponse
    {
        if ($supplier->systems()->exists()) {
            return redirect()
                ->route('supplier.index')
                ->with('error', 'Cannot delete a vendor that has systems assigned. Reassign or remove systems first.');
        }

        if ($supplier->projects()->exists()) {
            return redirect()
                ->route('supplier.index')
                ->with('error', 'Cannot delete a vendor that has projects assigned. Reassign or remove projects first.');
        }

        $supplier->delete();

        return redirect()
            ->route('supplier.index')
            ->with('success', 'Vendor deleted successfully.');
    }
}
