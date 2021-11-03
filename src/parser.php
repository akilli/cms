<?php
declare(strict_types=1);

namespace parser;

/**
 * Searches for custom placeholder elements, i. e. `<{custom-tag} id="{entity_id}-{id}"></{custom-tag}>`, and returns an
 * array of referenced IDs grouped by entities, i. e. ['{entity_id}' => [{id}, ...], ...]
 */
function tag(string $html, string $tag, array $entityIds = null): array
{
    $data = [];
    $entityPattern = $entityIds ? implode('|', $entityIds) : '[a-z][a-z_\.]*';
    $pattern = sprintf('#<%1$s id="(%2$s)-(\d+)">(?:[^<]*)</%1$s>#s', $tag, $entityPattern);

    if (preg_match_all($pattern, $html, $match)) {
        foreach ($match[1] as $key => $entityId) {
            if (!in_array($match[2][$key], $data[$entityId] ?? [])) {
                $data[$entityId][] = $match[2][$key];
            }
        }
    }

    return $data;
}

/**
 * Extracts attributes from given string
 */
function attr(string $str): array
{
    return preg_match_all('#([\w\-]+)\s*=\s*"([^"]+)"#', $str, $match) ? array_combine($match[1], $match[2]) : [];
}
