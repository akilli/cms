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
    $code = $attr['id'];

    if (!empty($item[$code]) && !media_delete($item[$code])) {
        $item['_error'][$code] = _('Could not delete old file %s', $item[$code]);

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
