<?php

namespace App\Http\Controllers;

use App\Models\Api;
use App\Models\Bpmn;
use App\Models\Domain;
use App\Models\Project;
use App\Models\System;
use App\Models\Vendor;
use App\Support\ApiTypes;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $stats = [
            'apis' => Api::count(),
            'by_type' => collect(ApiTypes::ALL)->mapWithKeys(fn ($t) => [
                $t => Api::where('type', $t)->count(),
            ])->filter(fn ($c) => $c > 0),
            'systems' => System::count(),
            'domains' => Domain::withCount('systems')->orderBy('name')->get(),
            'vendors' => Vendor::count(),
            'projects' => Project::count(),
            'bpmns' => Bpmn::count(),
            'integrations' => DB::table('api_system')->count(),
        ];

        $recentApis = Api::with(['ownerSystem.vendor', 'latestTps'])
            ->latest()
            ->take(5)
            ->get();

        $topTpsApis = Api::with(['ownerSystem', 'latestTps'])
            ->whereHas('latestTps')
            ->get()
            ->sortByDesc(fn (Api $api) => $api->current_tps ?? 0)
            ->take(5)
            ->values();

        $systems = System::withCount('ownedApis')
            ->with(['vendor', 'domain'])
            ->orderBy('name')
            ->take(6)
            ->get();

        $vendors = Vendor::withCount(['systems', 'apis'])
            ->orderBy('name')
            ->get();

        return view('home', compact('stats', 'recentApis', 'topTpsApis', 'systems', 'vendors'));
    }
}
