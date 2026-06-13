<?php

namespace App\Http\Controllers;

use App\Models\Server;
use App\Models\System;
use App\Support\ServerTypes;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ServerController extends Controller
{
    public function catalog(Request $request): View
    {
        $query = Server::with(['system.vendor'])
            ->orderBy('server_type')
            ->orderBy('name');

        if ($type = $request->get('server_type')) {
            $query->where('server_type', $type);
        }

        if ($systemId = $request->integer('system_id')) {
            $query->where('system_id', $systemId);
        }

        if ($search = trim((string) $request->get('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('hostname', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        $servers = $query->get();
        $systems = System::with('vendor')->orderBy('name')->get(['id', 'name', 'vendor_id']);
        $serverTypes = ServerTypes::ALL;

        $stats = [
            'total' => Server::count(),
            'by_type' => Server::selectRaw('server_type, count(*) as count')
                ->groupBy('server_type')
                ->pluck('count', 'server_type'),
            'ssl_expiring' => Server::whereNotNull('ssl_expires_at')
                ->whereBetween('ssl_expires_at', [now(), now()->addDays(30)])
                ->count(),
            'ssl_expired' => Server::whereNotNull('ssl_expires_at')
                ->where('ssl_expires_at', '<', now())
                ->count(),
        ];

        return view('infrastructure.index', compact('servers', 'systems', 'serverTypes', 'stats'));
    }

    public function index(System $system): View
    {
        $system->load(['vendor', 'parent', 'servers' => fn ($q) => $q->orderBy('server_type')->orderBy('name')]);

        $serversByType = $system->servers->groupBy('server_type');
        $serverTypes = ServerTypes::ALL;

        return view('systems.servers', compact('system', 'serversByType', 'serverTypes'));
    }

    public function store(Request $request, System $system): RedirectResponse
    {
        $validated = $this->validateServer($request);
        $system->servers()->create($validated);

        return redirect()
            ->route('systems.servers', $system)
            ->with('success', 'Server added successfully.');
    }

    public function update(Request $request, System $system, Server $server): RedirectResponse
    {
        $this->ensureServerBelongsToSystem($system, $server);

        $server->update($this->validateServer($request));

        return redirect()
            ->route('systems.servers', $system)
            ->with('success', 'Server updated successfully.');
    }

    public function destroy(System $system, Server $server): RedirectResponse
    {
        $this->ensureServerBelongsToSystem($system, $server);

        $server->delete();

        return redirect()
            ->route('systems.servers', $system)
            ->with('success', 'Server removed successfully.');
    }

    /** @return array<string, mixed> */
    private function validateServer(Request $request): array
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'server_type' => ServerTypes::validationRule(),
            'hostname' => 'nullable|string|max:255',
            'ip_address' => 'nullable|string|max:45',
            'port' => 'nullable|integer|min:1|max:65535',
            'location' => 'nullable|string|max:255',
            'ram' => 'nullable|string|max:100',
            'cpu' => 'nullable|string|max:100',
            'nic' => 'nullable|string|max:255',
            'ssl_certificate' => 'nullable|string',
            'ssl_issued_at' => 'nullable|date',
            'ssl_expires_at' => 'nullable|date|after_or_equal:ssl_issued_at',
            'notes' => 'nullable|string',
        ]);

        return $validated;
    }

    private function ensureServerBelongsToSystem(System $system, Server $server): void
    {
        if ((int) $server->system_id !== (int) $system->id) {
            abort(404);
        }
    }
}
