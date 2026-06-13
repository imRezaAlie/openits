<?php

namespace App\Http\Controllers;

use App\Models\Bpmn;
use App\Models\Project;
use App\Models\Vendor;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::all();
        $vendors = Vendor::all();
        return view('projects.index', compact('vendors', 'projects'));
    }

    public function create()
    {

    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'vendor_id' => 'required|integer|max:255',
            'status' => 'required|string|max:255',
        ]);

        // Store the vendor
        Project::create([
            'name' => $validatedData['name'],
            'vendor_id' => $validatedData['vendor_id'],
            'status' => $validatedData['status'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Project added successfully!',
        ]);
    }

    public function show(Project $project)
    {
        return view('projects.view', compact('project'));
    }

    public function edit(Project $project)
    {
        return view('projects.edit', compact('project'));
    }

    public function update(Request $request, Project $project)
    {
        $request->validate([
            'name' => 'required',
            'vendor_id' => 'required',
            'status' => 'required',

        ]);

        $project->update($request->only('name'));

        $project->update([
            'name' => $request->name,
            'vendor_id' => $request->vendor_id,
            'status' => $request->status,
        ]);

        return redirect()->route('project.index')->with('success', 'Project updated successfully.');
    }

    public function destroy(Project $project)
    {
        $project->delete();
        return redirect()->route('project.index')->with('success', 'Project deleted successfully.');
    }

    public function restore($id)
    {
        $project = Project::withTrashed()->findOrFail($id);
        $project->restore();
        return redirect()->route('project.index')->with('success', 'Project restored successfully.');
    }

    public function forceDelete($id)
    {
        $project = Project::withTrashed()->findOrFail($id);
        $project->forceDelete();
        return redirect()->route('project.index')->with('success', 'Project permanently deleted.');
    }
}
