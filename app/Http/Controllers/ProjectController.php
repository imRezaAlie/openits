<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Vendor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProjectController extends Controller
{
    private const STATUSES = ['active', 'development', 'retired', 'review', 'clarification'];

    public function index(): View
    {
        $projects = Project::with('vendor')
            ->withCount('bpmns')
            ->orderBy('name')
            ->get();

        $vendors = Vendor::orderBy('name')->get();

        return view('projects.index', [
            'vendors' => $vendors,
            'projects' => $projects,
            'statuses' => self::STATUSES,
        ]);
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('project.index');
    }

    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'vendor_id' => 'required|exists:vendors,id',
            'status' => ['required', Rule::in(self::STATUSES)],
        ]);

        Project::create($validated);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Project added successfully!',
            ]);
        }

        return redirect()
            ->route('project.index')
            ->with('success', 'Project created successfully.');
    }

    public function show(Project $project): View
    {
        $project->load(['vendor', 'bpmns']);

        return view('projects.view', [
            'project' => $project,
            'statuses' => self::STATUSES,
        ]);
    }

    public function edit(Project $project): View
    {
        $vendors = Vendor::orderBy('name')->get();

        return view('projects.edit', [
            'project' => $project,
            'vendors' => $vendors,
            'statuses' => self::STATUSES,
        ]);
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'vendor_id' => 'required|exists:vendors,id',
            'status' => ['required', Rule::in(self::STATUSES)],
        ]);

        $project->update($validated);

        return redirect()
            ->route('project.index')
            ->with('success', 'Project updated successfully.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        if ($project->bpmns()->exists()) {
            return redirect()
                ->route('project.index')
                ->with('error', 'Cannot delete a project that has linked processes. Remove or reassign them first.');
        }

        $project->delete();

        return redirect()
            ->route('project.index')
            ->with('success', 'Project deleted successfully.');
    }

    public function restore($id): RedirectResponse
    {
        $project = Project::withTrashed()->findOrFail($id);
        $project->restore();

        return redirect()
            ->route('project.index')
            ->with('success', 'Project restored successfully.');
    }

    public function forceDelete($id): RedirectResponse
    {
        $project = Project::withTrashed()->findOrFail($id);
        $project->forceDelete();

        return redirect()
            ->route('project.index')
            ->with('success', 'Project permanently deleted.');
    }
}
