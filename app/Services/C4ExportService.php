<?php

namespace App\Services;

use App\Models\C4Relationship;
use App\Models\System;
use App\Support\C4ContainerTypes;

class C4ExportService
{
    public function toStructurizrDsl(System $system): string
    {
        $system->loadMissing(['c4Context', 'c4Containers.components']);

        $lines = [];
        $lines[] = 'workspace {';
        $lines[] = '    model {';
        $lines[] = '        user = person "End User"';
        $lines[] = sprintf('        %s = softwareSystem "%s" "%s"', $this->slug($system->name), $this->escape($system->name), $this->escape($system->description ?? ''));

        foreach ($system->c4Context?->external_systems ?? [] as $external) {
            $slug = $this->slug($external['name'] ?? 'external');
            $lines[] = sprintf('        %s = softwareSystem "%s" "%s" "External"', $slug, $this->escape($external['name'] ?? 'External'), $this->escape($external['description'] ?? ''));
        }

        $systemSlug = $this->slug($system->name);

        foreach ($system->c4Containers as $container) {
            $containerSlug = $this->slug($container->name);
            $lines[] = sprintf(
                '        %s = container "%s" "%s" "%s" "%s"',
                $containerSlug,
                $this->escape($container->name),
                $this->escape($container->description ?? ''),
                $this->escape(C4ContainerTypes::label($container->type)),
                $this->escape($container->technology ?? ''),
            );

            foreach ($container->components as $component) {
                $componentSlug = $this->slug($component->name);
                $lines[] = sprintf(
                    '        %s = component "%s" "%s" "%s"',
                    $componentSlug,
                    $this->escape($component->name),
                    $this->escape($component->description ?? ''),
                    $this->escape($component->technology ?? ''),
                );
            }
        }

        $lines[] = '    }';
        $lines[] = '';
        $lines[] = '    views {';
        $lines[] = sprintf('        systemContext %s "Context" {', $systemSlug);
        $lines[] = '            include *';
        $lines[] = '            autolayout lr';
        $lines[] = '        }';
        $lines[] = sprintf('        container %s "Containers" {', $systemSlug);
        $lines[] = '            include *';
        $lines[] = '            autolayout lr';
        $lines[] = '        }';
        $lines[] = '    }';
        $lines[] = '}';

        return implode("\n", $lines);
    }

    public function toDrawIoXml(System $system): string
    {
        $system->loadMissing(['c4Context', 'c4Containers.components']);

        $cellId = 2;
        $cells = [];
        $nodeIdMap = [];
        $x = 40;
        $y = 40;

        $cells[] = $this->drawIoCell(0, null, '', 'root');
        $cells[] = $this->drawIoCell(1, 0, '', 'layer');

        foreach ($system->c4Containers as $container) {
            $pos = $container->position ?? ['x' => $x, 'y' => $y];
            $style = $this->drawIoContainerStyle($container->type);
            $cells[] = $this->drawIoCell(
                $cellId,
                1,
                htmlspecialchars($container->name, ENT_XML1),
                'rounded=1;whiteSpace=wrap;html=1;'.$style,
                (int) ($pos['x'] ?? $x),
                (int) ($pos['y'] ?? $y),
                180,
                72,
            );
            $nodeIdMap[$container->id] = $cellId;
            $cellId++;
            $y += 100;
        }

        $relationships = C4Relationship::query()
            ->whereIn('source_id', array_keys($nodeIdMap))
            ->orWhereIn('target_id', array_keys($nodeIdMap))
            ->get();

        foreach ($relationships as $rel) {
            $sourceCell = $nodeIdMap[$rel->source_id] ?? null;
            $targetCell = $nodeIdMap[$rel->target_id] ?? null;
            if (! $sourceCell || ! $targetCell) {
                continue;
            }
            $label = htmlspecialchars($rel->protocol ?? '', ENT_XML1);
            $cells[] = sprintf(
                '<mxCell id="%d" parent="1" source="%d" target="%d" value="%s" edge="1" style="endArrow=classic;html=1;rounded=0;"><mxGeometry relative="1" as="geometry"/></mxCell>',
                $cellId,
                $sourceCell,
                $targetCell,
                $label,
            );
            $cellId++;
        }

        $diagramName = htmlspecialchars($system->name.' C4', ENT_XML1);

        return '<?xml version="1.0" encoding="UTF-8"?>'
            .'<mxfile host="OpenITS" modified="'.now()->toIso8601String().'" agent="OpenITS C4" version="22.1.0">'
            .'<diagram name="'.$diagramName.'">'
            .'<mxGraphModel dx="1200" dy="800" grid="1" gridSize="24" guides="1" tooltips="1" connect="1" arrows="1" fold="1" page="1" pageScale="1" pageWidth="1600" pageHeight="1200">'
            .'<root>'.implode('', $cells).'</root>'
            .'</mxGraphModel>'
            .'</diagram>'
            .'</mxfile>';
    }

    private function drawIoCell(
        int $id,
        ?int $parent,
        string $value,
        string $style,
        int $x = 0,
        int $y = 0,
        int $w = 0,
        int $h = 0,
    ): string {
        if ($parent === null) {
            return sprintf('<mxCell id="%d"/>', $id);
        }
        if ($w === 0) {
            return sprintf('<mxCell id="%d" parent="%d" value="%s" style="%s"/>', $id, $parent, $value, $style);
        }

        return sprintf(
            '<mxCell id="%d" parent="%d" value="%s" style="%s" vertex="1"><mxGeometry x="%d" y="%d" width="%d" height="%d" as="geometry"/></mxCell>',
            $id,
            $parent,
            $value,
            $style,
            $x,
            $y,
            $w,
            $h,
        );
    }

    private function drawIoContainerStyle(string $type): string
    {
        return match ($type) {
            C4ContainerTypes::DATABASE => 'fillColor=#dae8fc;strokeColor=#6c8ebf;',
            C4ContainerTypes::FRONTEND => 'fillColor=#d5e8d4;strokeColor=#82b366;',
            C4ContainerTypes::EVENT_BUS => 'fillColor=#fff2cc;strokeColor=#d6b656;',
            C4ContainerTypes::API_GATEWAY => 'fillColor=#e1d5e7;strokeColor=#9673a6;',
            default => 'fillColor=#f5f5f5;strokeColor=#666666;',
        };
    }

    public function toPlantUml(System $system): string
    {
        $system->loadMissing(['c4Context', 'c4Containers.components']);

        $lines = ['@startuml', '!include https://raw.githubusercontent.com/plantuml-stdlib/C4-PlantUML/master/C4_Container.puml', ''];

        $lines[] = sprintf('System(%s, "%s", "%s")', $this->slug($system->name), $this->escape($system->name), $this->escape($system->description ?? ''));

        foreach ($system->c4Containers as $container) {
            $lines[] = sprintf(
                'Container(%s, "%s", "%s", "%s")',
                $this->slug($container->name),
                $this->escape($container->name),
                $this->escape($container->technology ?? ''),
                $this->escape($container->description ?? ''),
            );
        }

        $lines[] = '@enduml';

        return implode("\n", $lines);
    }

    /**
     * @return array<string, mixed>
     */
    public function toJson(System $system): array
    {
        $system->loadMissing(['c4Context', 'c4Containers.components', 'domain', 'vendor']);

        return [
            'exported_at' => now()->toIso8601String(),
            'system' => [
                'id' => $system->id,
                'name' => $system->name,
                'description' => $system->description,
                'domain' => $system->domain?->name,
                'vendor' => $system->vendor?->name,
            ],
            'context' => $system->c4Context,
            'containers' => $system->c4Containers->map(fn ($c) => [
                ...$c->toArray(),
                'components' => $c->components,
            ]),
        ];
    }

    private function slug(string $name): string
    {
        return preg_replace('/[^a-zA-Z0-9_]/', '_', strtolower($name)) ?: 'element';
    }

    private function escape(string $value): string
    {
        return str_replace('"', '\\"', $value);
    }
}
