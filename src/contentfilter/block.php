<?php
declare(strict_types=1);

namespace contentfilter;

use entity;
use layout;

/**
 * Replaces all DB placeholder tags, i.e. `<editor-block id="{entity_id}-{id}"></editor-block>`, with actual blocks
 */
function block(string $html): string
{
    $pattern = '#<editor-block id="%s"(?:[^>]*)>\s*</editor-block>#s';

    if (preg_match_all(sprintf($pattern, '([a-z_]+)-(\d+)'), $html, $match)) {
        $data = [];

        foreach ($match[1] as $key => $entityId) {
            $data[$entityId][] = $match[2][$key];
        }

        foreach ($data as $entityId => $ids) {
            foreach (entity\all($entityId, [['id', $ids]]) as $item) {
                $html = preg_replace(
                    sprintf($pattern, $entityId . '-' . $item['id']),
                    layout\render(layout\db_cfg($item)),
                    $html
                );
            }
        }
    }

    return preg_replace('#<editor-block(?:[^>]*)>\s*</editor-block>#s', '', $html);
}
