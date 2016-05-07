<?php
namespace qnd;

/**
 * Attribute viewer
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function attribute_viewer(array $attr, array $item): string
{
    if (!viewable($attr)) {
        return '';
    }

    $item[$attr['id']] = value($attr, $item);

    if (in_array($attr['frontend'], ['checkbox', 'radio', 'select'])) {
        return attribute_viewer_option($attr, $item);
    }

    $callback = fqn('attribute_viewer_' . $attr['type']);

    return is_callable($callback) ? $callback($attr, $item) : (string) encode($item[$attr['id']]);
}

/**
 * Option attribute viewer
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function attribute_viewer_option(array $attr, array $item): string
{
    if (!$attr['options'] = option($attr)) {
        return '';
    }

    $values = [];

    foreach ((array) $item[$attr['id']] as $v) {
        if (!empty($attr['options'][$v])) {
            if (is_array($attr['options'][$v]) && !empty($attr['options'][$v]['name'])) {
                $values[] = $attr['options'][$v]['name'];
            } elseif (is_scalar($attr['options'][$v])) {
                $values[] = $attr['options'][$v];
            }
        }
    }

    return encode(implode(', ', $values));
}

/**
 * Date attribute viewer
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function attribute_viewer_date(array $attr, array $item): string
{
    return empty($item[$attr['id']]) ? '' : date_format(date_create($item[$attr['id']]), config('i18n.date'));
}

/**
 * Datetime attribute viewer
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function attribute_viewer_datetime(array $attr, array $item): string
{
    return empty($item[$attr['id']]) ? '' : date_format(date_create($item[$attr['id']]), config('i18n.datetime'));
}

/**
 * Rich text editor attribute viewer
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function attribute_viewer_rte(array $attr, array $item): string
{
    return $item[$attr['id']];
}

/**
 * Audio attribute viewer
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function attribute_viewer_audio(array $attr, array $item): string
{
    if ($item[$attr['id']] && media_load($item[$attr['id']])) {
        return '<audio src="' . url_media($item[$attr['id']]) . '" controls="controls"></audio>';
    }

    return '';
}

/**
 * Embed attribute viewer
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function attribute_viewer_embed(array $attr, array $item): string
{
    if ($item[$attr['id']] && media_load($item[$attr['id']])) {
        return '<embed src="' . url_media($item[$attr['id']]) . '" autoplay="no" loop="no" />';
    }

    return '';
}

/**
 * File attribute viewer
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function attribute_viewer_file(array $attr, array $item): string
{
    if ($item[$attr['id']] && media_load($item[$attr['id']])) {
        return '<a href="' . url_media($item[$attr['id']]) . '">' . $item[$attr['id']] . '</a>';
    }

    return '';
}

/**
 * Image attribute viewer
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function attribute_viewer_image(array $attr, array $item): string
{
    if ($item[$attr['id']] && ($file = media_load($item[$attr['id']]))) {
        return '<img src="' . image($file, $attr['action']) . '" alt="' . $item[$attr['id']] . '" />';
    }

    return '';
}

/**
 * Video attribute viewer
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function attribute_viewer_video(array $attr, array $item): string
{
    if ($item[$attr['id']] && media_load($item[$attr['id']])) {
        return '<video src="' . url_media($item[$attr['id']]) . '" controls="controls"></video>';
    }

    return '';
}
