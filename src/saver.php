<?php
namespace akilli;

/**
 * Save
 *
 * @return bool
 */
function saver(): bool
{
    return true;
}

/**
 * Save password
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function saver_password(array $attr, array & $item): bool
{
    $code = $attr['id'];

    if (!empty($item[$code]) && is_string($item[$code])) {
        $item[$code] = password_hash($item[$code], PASSWORD_DEFAULT);
    }

    return true;
}

/**
 * Save multiple
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function saver_multiple(array $attr, array & $item): bool
{
    $item[$attr['id']] = json_encode(array_filter(array_map('trim', (array) $item[$attr['id']])));

    return true;
}

/**
 * Save search index
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function saver_index(array $attr, array & $item): bool
{
    $code = $attr['id'];
    $item[$code] = '';

    foreach ($item['_meta']['attributes'] as $a) {
        if ($a['is_searchable'] || meta_action(['view', 'index', 'list'], $a)) {
            $item[$code] .= ' ' . str_replace("\n", '', strip_tags($item[$a['id']]));
        }
    }

    return true;
}
