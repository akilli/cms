<?php
declare(strict_types=1);

namespace filter\block;

use entity;
use layout;

/**
 * Replaces all DB placeholder tags, i.e. `<app-block id="{entity_id}-{id}"></app-block>`, with actual blocks
 */
function filter(string $html): string
{
    $pattern = '#<app-block id="%s"(?:[^>]*)>\s*</app-block>#s';

    if (preg_match_all(sprintf($pattern, '([a-z_]+)-(\d+)'), $html, $match)) {
        $data = [];

        foreach ($match[1] as $key => $entityId) {
            $data[$entityId][] = $match[2][$key];
        }

        foreach ($data as $entityId => $ids) {
            foreach (entity\all($entityId, crit: [['id', $ids]]) as $item) {
                $html = preg_replace(sprintf($pattern, $entityId . '-' . $item['id']), layout\render_data($item), $html);
            }
        }
    }

    return preg_replace('#<app-block(?:[^>]*)>\s*</app-block>#s', '', $html);
}
