<?php

namespace App\Http\Controllers;

use App\Models\Api;
use App\Models\ApiVersion;
use App\Support\ApiTypes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ApiVersionController extends Controller
{
    public function store(Request $request, Api $api): RedirectResponse|JsonResponse
    {
        $validated = $request->validate($this->validationRules($api));

        $isDefault = (bool) ($validated['is_default'] ?? false);

        if ($isDefault) {
            $api->versions()->update(['is_default' => false]);
        } elseif (! $api->versions()->exists()) {
            $isDefault = true;
        }

        $version = $api->versions()->create([
            ...collect($validated)->only([
                'version', 'endpoint_url', 'description', 'request_format',
                'response_format', 'authentication_type', 'status',
            ])->toArray(),
            'is_default' => $isDefault,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Version created successfully.',
                'version_id' => $version->id,
            ]);
        }

        return redirect()
            ->route('apis.show', ['api' => $api, 'version' => $version->id])
            ->with('success', 'Version '.$version->version.' created successfully.');
    }

    public function update(Request $request, Api $api, ApiVersion $version): RedirectResponse|JsonResponse
    {
        $this->ensureVersionBelongsToApi($api, $version);

        $validated = $request->validate($this->validationRules($api, $version));

        $isDefault = (bool) ($validated['is_default'] ?? $version->is_default);

        if ($isDefault && ! $version->is_default) {
            $api->versions()->where('id', '!=', $version->id)->update(['is_default' => false]);
        }

        $version->update(collect($validated)->only([
            'version', 'endpoint_url', 'description', 'request_format',
            'response_format', 'authentication_type', 'status', 'is_default',
        ])->merge(['is_default' => $isDefault])->toArray());

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Version updated successfully.']);
        }

        return redirect()
            ->route('apis.show', ['api' => $api, 'version' => $version->id])
            ->with('success', 'Version updated successfully.');
    }

    public function destroy(Api $api, ApiVersion $version): RedirectResponse|JsonResponse
    {
        $this->ensureVersionBelongsToApi($api, $version);

        if ($api->versions()->count() <= 1) {
            $message = 'Cannot delete the only version of an API.';

            if (request()->expectsJson()) {
                return response()->json(['success' => false, 'message' => $message], 422);
            }

            return redirect()->route('apis.show', $api)->with('error', $message);
        }

        $wasDefault = $version->is_default;
        $version->delete();

        if ($wasDefault) {
            $api->versions()->orderBy('version')->first()?->update(['is_default' => true]);
        }

        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Version deleted successfully.']);
        }

        return redirect()->route('apis.show', $api)->with('success', 'Version deleted successfully.');
    }

    public function setDefault(Api $api, ApiVersion $version): RedirectResponse|JsonResponse
    {
        $this->ensureVersionBelongsToApi($api, $version);

        $api->versions()->update(['is_default' => false]);
        $version->update(['is_default' => true]);

        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Default version updated.']);
        }

        return redirect()
            ->route('apis.show', ['api' => $api, 'version' => $version->id])
            ->with('success', 'Default version set to '.$version->version.'.');
    }

    /** @return array<string, mixed> */
    private function validationRules(Api $api, ?ApiVersion $existing = null): array
    {
        $uniqueRule = 'unique:api_versions,version,'.($existing?->id ?? 'NULL').',id,api_id,'.$api->id;

        return [
            'version' => ['required', 'string', 'max:50', $uniqueRule],
            'endpoint_url' => 'nullable|string|max:2048',
            'description' => 'nullable|string',
            'request_format' => 'nullable|string|max:50',
            'response_format' => 'nullable|string|max:50',
            'authentication_type' => 'nullable|string|max:100',
            'status' => 'required|in:active,deprecated,draft',
            'is_default' => 'nullable|boolean',
        ];
    }

    private function ensureVersionBelongsToApi(Api $api, ApiVersion $version): void
    {
        if ((int) $version->api_id !== (int) $api->id) {
            abort(404);
        }
    }
}
