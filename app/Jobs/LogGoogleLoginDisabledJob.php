<?php

namespace App\Jobs;

use App\Services\SettingsService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

/**
 * Monitoring job that logs when Google login remains disabled.
 */
class LogGoogleLoginDisabledJob implements ShouldQueue
{
    use Queueable;

    /**
     * Execute the job.
     */
    public function handle(SettingsService $settings): void
    {
        if ($settings->isGoogleLoginEnabled()) {
            return;
        }

        Log::info('Google login is currently disabled.', [
            'credentials_configured' => $settings->googleCredentialsConfigured(),
            'checked_at' => now()->toIso8601String(),
        ]);
    }
}
