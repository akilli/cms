<?php
declare(strict_types = 1);

namespace loader;

/**
 * JSON loader
 */
function json(array $attr, array $data): array
{
    if ($data[$attr['id']] && is_string($data[$attr['id']])) {
        $data[$attr['id']] = json_decode($data[$attr['id']], true);
    }

    return is_array($data[$attr['id']]) ? $data[$attr['id']] : [];
}
