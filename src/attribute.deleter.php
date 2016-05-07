<?php
namespace qnd;

/**
 * Attribute deleter
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function attribute_deleter(array $attr, array & $item): bool
{
    $callback = fqn('attribute_deleter_' . $attr['type']);

    return is_callable($callback) ? $callback($attr, $item) : true;
}

/**
 * File attribute deleter
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function attribute_deleter_file(array $attr, array & $item): bool
{
    if (!empty($item[$attr['id']]) && !media_delete($item[$attr['id']])) {
        $item['_error'][$attr['id']] = _('Could not delete old file %s', $item[$attr['id']]);
        return false;
    }

    return true;
}

/**
 * Audio attribute deleter
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function attribute_deleter_audio(array $attr, array & $item): bool
{
    return attribute_deleter_file($attr, $item);
}

/**
 * Embed attribute deleter
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function attribute_deleter_embed(array $attr, array & $item): bool
{
    return attribute_deleter_file($attr, $item);
}

/**
 * Image attribute deleter
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function attribute_deleter_image(array $attr, array & $item): bool
{
    return attribute_deleter_file($attr, $item);
}

/**
 * Video attribute deleter
 *
 * @param array $attr
 * @param array $item
 *
 * @return bool
 */
function attribute_deleter_video(array $attr, array & $item): bool
{
    return attribute_deleter_file($attr, $item);
}
