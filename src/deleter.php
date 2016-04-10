<?php
namespace akilli;

/**
 * Delete
 *
 * @return bool
 */
function deleter(): bool
{
    return true;
}

/**
 * Delete file
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function deleter_file(array $attr, array & $item): bool
{
    $code = $attr['id'];

    if (!empty($item[$code]) && !media_delete($item[$code])) {
        $item['_error'][$code] = _('Could not delete old file %s', $item[$code]);

        return false;
    }

    return true;
}
