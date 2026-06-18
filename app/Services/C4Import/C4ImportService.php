<?php

namespace App\Services\C4Import;

use App\Models\C4Import;
use App\Models\System;
use App\Support\C4ImportTypes;
use Illuminate\Support\Facades\Storage;

class C4ImportService
{
    public function __construct(
        private C4OpenApiImportService $openApiImporter,
        private C4AsyncApiImportService $asyncApiImporter,
        private C4StructurizrImportService $structurizrImporter,
        private C4JsonBackupImportService $jsonBackupImporter,
    ) {}

    /**
     * @param  array<string, mixed>  $options
     */
    public function createImport(
        System $system,
        int $userId,
        string $type,
        string $storedPath,
        string $originalFilename,
        array $options = [],
    ): C4Import {
        return C4Import::create([
            'system_id' => $system->id,
            'user_id' => $userId,
            'type' => $type,
            'status' => C4Import::STATUS_PENDING,
            'file_path' => $storedPath,
            'original_filename' => $originalFilename,
            'options' => $options,
        ]);
    }

    public function process(C4Import $import): void
    {
        $import->markProcessing();

        $content = Storage::disk('local')->get($import->file_path);
        if ($content === null) {
            $import->markFailed('Import file not found.');

            return;
        }

        $system = $import->system;

        try {
            $result = match ($import->type) {
                C4ImportTypes::OPENAPI => $this->openApiImporter->import($system, $content, $import),
                C4ImportTypes::ASYNCAPI => $this->asyncApiImporter->import($system, $content, $import),
                C4ImportTypes::STRUCTURIZR => $this->structurizrImporter->import($system, $content, $import),
                C4ImportTypes::JSON_BACKUP => $this->jsonBackupImporter->import($system, $content, $import),
                default => throw new \InvalidArgumentException('Unsupported import type: '.$import->type),
            };

            $import->markCompleted($result);
        } catch (\Throwable $e) {
            $import->markFailed($e->getMessage());
        } finally {
            Storage::disk('local')->delete($import->file_path);
        }
    }
}
