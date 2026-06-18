<?php

namespace App\Console\Commands;

use App\Services\SettingsService;
use Illuminate\Console\Command;

class GoogleLoginStatusCommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'google:login:status
                            {--enable : Enable Google login}
                            {--disable : Disable Google login}';

    /**
     * @var string
     */
    protected $description = 'Check or update the Google login enabled status';

    public function __construct(
        protected SettingsService $settings
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if ($this->option('enable') && $this->option('disable')) {
            $this->error('Use either --enable or --disable, not both.');

            return self::FAILURE;
        }

        if ($this->option('enable')) {
            if (! $this->settings->googleCredentialsConfigured()) {
                $this->error(__('google.errors.credentials_missing'));

                return self::FAILURE;
            }

            $this->settings->setGoogleLoginEnabled(true);
            $this->info(__('google.messages.enabled'));
        } elseif ($this->option('disable')) {
            $this->settings->setGoogleLoginEnabled(false);
            $this->info(__('google.messages.disabled'));
        }

        $enabled = $this->settings->isGoogleLoginEnabled();
        $credentialsConfigured = $this->settings->googleCredentialsConfigured();

        $this->table(
            ['Setting', 'Value'],
            [
                ['Google login enabled', $enabled ? 'Yes' : 'No'],
                ['Credentials configured', $credentialsConfigured ? 'Yes' : 'No'],
                ['Client ID present', filled(config('services.google.client_id')) ? 'Yes' : 'No'],
                ['Redirect URI', config('services.google.redirect') ?? '—'],
            ]
        );

        return self::SUCCESS;
    }
}
