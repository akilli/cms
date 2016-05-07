<?php
namespace qnd;

/**
 * Attribute saver
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function attribute_saver(array $attr, array & $item): bool
{
    $callback = fqn('attribute_saver_' . $attr['type']);

    return is_callable($callback) ? $callback($attr, $item) : true;
}

/**
 * Password attribute saver
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function attribute_saver_password(array $attr, array & $item): bool
{
    $code = $attr['id'];

    if (!empty($item[$code]) && is_string($item[$code])) {
        $item[$code] = password_hash($item[$code], PASSWORD_DEFAULT);
    }

    return true;
}

/**
 * Multicheckbox attribute saver
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function attribute_saver_multicheckbox(array $attr, array & $item): bool
{
    $item[$attr['id']] = json_encode(array_filter(array_map('trim', (array) $item[$attr['id']])));

    return true;
}

/**
 * Multiselect attribute saver
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function attribute_saver_multiselect(array $attr, array & $item): bool
{
    return attribute_saver_multicheckbox($attr, $item);
}

/**
 * Index attribute saver
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function attribute_saver_index(array $attr, array & $item): bool
{
    $code = $attr['id'];
    $item[$code] = '';

    foreach ($item['_meta']['attributes'] as $a) {
        if ($a['searchable']) {
            $item[$code] .= ' ' . str_replace("\n", '', strip_tags($item[$a['id']]));
        }
    }

    return true;
}
