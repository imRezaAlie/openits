<?php

namespace App\Services\C4Import;

use Symfony\Component\Yaml\Yaml;

trait ParsesSpecFiles
{
    /**
     * @return array<string, mixed>
     */
    protected function parseSpecContent(string $content): array
    {
        $trimmed = ltrim($content);

        if (str_starts_with($trimmed, '{')) {
            $decoded = json_decode($content, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \InvalidArgumentException('Invalid JSON: '.json_last_error_msg());
            }

            return $decoded;
        }

        return Yaml::parse($content) ?? [];
    }
}
