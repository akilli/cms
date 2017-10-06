<?php
declare(strict_types = 1);

namespace cms;

/**
 * Loader
 *
 * @return mixed
 */
function loader(array $attr, array $data)
{
    $data[$attr['id']] = cast($attr, $data[$attr['id']] ?? null);

    return $attr['loader'] ? $attr['loader']($attr, $data) : $data[$attr['id']];
}

/**
 * JSON loader
 */
function loader_json(array $attr, array $data): array
{
    return $data[$attr['id']] && ($val = json_decode($data[$attr['id']], true)) ? $val : [];
}
