<?php
namespace App\Http\Controllers;

use App\Models\Vendor;
use Illuminate\Http\Request;

class VendorController extends Controller
{
    public function index()
    {
        $vendors = Vendor::all();
        return view('vendors.index', compact('vendors'));
    }

    public function create()
    {

    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        // Store the vendor
        Vendor::create([
            'name' => $validatedData['name'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Vendor added successfully!',
        ]);
    }

    public function show(Vendor $vendor)
    {
        return view('vendors.view', compact('vendor'));
    }

    public function edit(Vendor $vendor)
    {
        return view('vendors.edit', compact('vendor'));
    }

    public function update(Request $request, Vendor $vendor)
    {
        $request->validate([
            'name' => 'required',
        ]);

        $vendor->update($request->only('name'));

        return redirect()->route('vendors.index')->with('success', 'Vendor updated successfully.');
    }

    public function destroy(Vendor $vendor)
    {
        $vendor->delete(); // Soft delete the vendor
        return redirect()->route('vendors.index')->with('success', 'Vendor deleted successfully.');
    }

    public function restore($id)
    {
        $vendor = Vendor::withTrashed()->findOrFail($id);
        $vendor->restore(); // Restore the soft-deleted vendor
        return redirect()->route('vendors.index')->with('success', 'Vendor restored successfully.');
    }

    public function forceDelete($id)
    {
        $vendor = Vendor::withTrashed()->findOrFail($id);
        $vendor->forceDelete(); // Permanently delete the vendor
        return redirect()->route('vendors.index')->with('success', 'Vendor permanently deleted.');
    }
}
