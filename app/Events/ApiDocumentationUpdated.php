<?php

namespace App\Events;

use App\Models\Api;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ApiDocumentationUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Api $api,
    ) {}
}
