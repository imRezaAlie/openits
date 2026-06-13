<?php

namespace App\Http\Controllers;

use App\Models\Api;
use App\Models\Domain;
use App\Models\System;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DomainController extends Controller
{
    public function index(): View
    {
        $totalSystems = System::count();
        $totalApis = Api::whereNotNull('owner_system_id')->count();
        $totalIntegrations = DB::table('api_system')
            ->join('apis', 'apis.id', '=', 'api_system.api_id')
            ->whereColumn('api_system.system_id', '!=', 'apis.owner_system_id')
            ->count();

        $crossDomainIntegrations = $this->countCrossDomainIntegrations();

        $domains = Domain::withCount('systems')
            ->orderBy('name')
            ->get()
            ->map(fn (Domain $domain) => $this->enrichDomainOverview($domain, $totalSystems, $totalApis));

        $overview = [
            'domains' => $domains->count(),
            'systems' => $totalSystems,
            'apis' => $totalApis,
            'integrations' => $totalIntegrations,
            'cross_domain_integrations' => $crossDomainIntegrations,
            'unassigned_systems' => System::whereNull('domain_id')->count(),
        ];

        $crossDomainLinks = $this->crossDomainLinks(limit: 8);

        return view('domains.index', compact('domains', 'overview', 'crossDomainLinks'));
    }

    public function show(Domain $domain): View
    {
        $domain->load([
            'systems' => fn ($q) => $q->with('vendor')->withCount('ownedApis')->orderBy('name'),
        ]);

        $domain->apis_count = Api::whereHas(
            'ownerSystem',
            fn ($q) => $q->where('domain_id', $domain->id)
        )->count();

        return view('domains.show', compact('domain'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:domains,name',
            'description' => 'nullable|string|max:2000',
            'icon' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:20',
        ]);

        $validated['slug'] = $this->uniqueSlug(Str::slug($validated['name']));

        Domain::create($validated);

        return redirect()
            ->route('domains.index')
            ->with('success', 'Domain created successfully.');
    }

    public function update(Request $request, Domain $domain): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('domains', 'name')->ignore($domain->id)],
            'description' => 'nullable|string|max:2000',
            'icon' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:20',
        ]);

        if ($validated['name'] !== $domain->name) {
            $validated['slug'] = $this->uniqueSlug(Str::slug($validated['name']), $domain->id);
        }

        $domain->update($validated);

        return redirect()
            ->route('domains.show', $domain)
            ->with('success', 'Domain updated successfully.');
    }

    public function destroy(Domain $domain): RedirectResponse
    {
        if ($domain->systems()->exists()) {
            return redirect()
                ->route('domains.index')
                ->with('error', 'Cannot delete a domain that has systems assigned. Reassign systems first.');
        }

        $domain->delete();

        return redirect()
            ->route('domains.index')
            ->with('success', 'Domain deleted successfully.');
    }

    private function uniqueSlug(string $base, ?int $ignoreId = null): string
    {
        $slug = $base;
        $counter = 1;

        while (Domain::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base.'-'.$counter++;
        }

        return $slug;
    }

    private function enrichDomainOverview(Domain $domain, int $totalSystems, int $totalApis): Domain
    {
        $ownerScope = fn ($q) => $q->where('domain_id', $domain->id);

        $domain->apis_count = Api::whereHas('ownerSystem', $ownerScope)->count();

        $domain->vendors_count = System::where('domain_id', $domain->id)
            ->whereNotNull('vendor_id')
            ->distinct()
            ->count('vendor_id');

        $domain->integrations_count = DB::table('api_system')
            ->join('apis', 'apis.id', '=', 'api_system.api_id')
            ->join('systems as owner', 'owner.id', '=', 'apis.owner_system_id')
            ->where('owner.domain_id', $domain->id)
            ->whereColumn('api_system.system_id', '!=', 'apis.owner_system_id')
            ->count();

        $domain->cross_domain_count = DB::table('api_system')
            ->join('apis', 'apis.id', '=', 'api_system.api_id')
            ->join('systems as owner', 'owner.id', '=', 'apis.owner_system_id')
            ->join('systems as consumer', 'consumer.id', '=', 'api_system.system_id')
            ->where('owner.domain_id', $domain->id)
            ->whereColumn('owner.domain_id', '!=', 'consumer.domain_id')
            ->whereColumn('api_system.system_id', '!=', 'apis.owner_system_id')
            ->count();

        $domain->systems_pct = $totalSystems > 0
            ? round(($domain->systems_count / $totalSystems) * 100, 1)
            : 0;

        $domain->apis_pct = $totalApis > 0
            ? round(($domain->apis_count / $totalApis) * 100, 1)
            : 0;

        $domain->by_type = Api::whereHas('ownerSystem', $ownerScope)
            ->selectRaw('type, COUNT(*) as aggregate')
            ->groupBy('type')
            ->pluck('aggregate', 'type');

        $domain->top_systems = System::where('domain_id', $domain->id)
            ->with('vendor')
            ->withCount('ownedApis')
            ->orderByDesc('owned_apis_count')
            ->orderBy('name')
            ->limit(3)
            ->get();

        return $domain;
    }

    private function countCrossDomainIntegrations(): int
    {
        return DB::table('api_system')
            ->join('apis', 'apis.id', '=', 'api_system.api_id')
            ->join('systems as owner', 'owner.id', '=', 'apis.owner_system_id')
            ->join('systems as consumer', 'consumer.id', '=', 'api_system.system_id')
            ->whereColumn('owner.domain_id', '!=', 'consumer.domain_id')
            ->whereColumn('api_system.system_id', '!=', 'apis.owner_system_id')
            ->count();
    }

    private function crossDomainLinks(int $limit = 8): Collection
    {
        return collect(DB::table('api_system')
            ->join('apis', 'apis.id', '=', 'api_system.api_id')
            ->join('systems as owner', 'owner.id', '=', 'apis.owner_system_id')
            ->join('systems as consumer', 'consumer.id', '=', 'api_system.system_id')
            ->join('domains as owner_domain', 'owner_domain.id', '=', 'owner.domain_id')
            ->join('domains as consumer_domain', 'consumer_domain.id', '=', 'consumer.domain_id')
            ->whereColumn('owner.domain_id', '!=', 'consumer.domain_id')
            ->whereColumn('api_system.system_id', '!=', 'apis.owner_system_id')
            ->select([
                'apis.id as api_id',
                'apis.name as api_name',
                'apis.type as api_type',
                'owner.name as owner_system',
                'consumer.name as consumer_system',
                'owner_domain.name as owner_domain',
                'owner_domain.color as owner_domain_color',
                'consumer_domain.name as consumer_domain',
                'consumer_domain.color as consumer_domain_color',
            ])
            ->orderBy('owner_domain.name')
            ->orderBy('apis.name')
            ->limit($limit)
            ->get());
    }
}
