<?php

namespace App\Jobs;

use App\Models\C4Import;
use App\Services\C4Import\C4ImportService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessC4ImportJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public int $timeout = 300;

    public function __construct(
        public C4Import $import,
    ) {}

    public function handle(C4ImportService $importService): void
    {
        $importService->process($this->import->fresh());
    }

    public function failed(?\Throwable $exception): void
    {
        $this->import->fresh()?->markFailed(
            $exception?->getMessage() ?? 'Import job failed unexpectedly.',
        );
    }
}
