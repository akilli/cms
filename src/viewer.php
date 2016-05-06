<?php
namespace qnd;

/**
 * Viewer
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function viewer(array $attr, array $item): string
{
    if (!viewable($attr)) {
        return '';
    }

    return $attr['viewer'] ? $attr['viewer']($attr, $item) : (string) encode(value($attr, $item));
}

/**
 * Option viewer
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function viewer_option(array $attr, array $item): string
{
    $value = value($attr, $item);

    if (!$attr['options'] = option($attr, $item)) {
        return '';
    }

    $values = [];

    foreach ((array) $value as $v) {
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
 * Date viewer
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function viewer_date(array $attr, array $item): string
{
    return empty($item[$attr['id']]) ? '' : date_format(date_create($item[$attr['id']]), config('i18n.date'));
}

/**
 * Datetime viewer
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function viewer_datetime(array $attr, array $item): string
{
    return empty($item[$attr['id']]) ? '' : date_format(date_create($item[$attr['id']]), config('i18n.datetime'));
}

/**
 * Rich text editor viewer
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function viewer_rte(array $attr, array $item): string
{
    return value($attr, $item);
}

/**
 * Audio viewer
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function viewer_audio(array $attr, array $item): string
{
    if (($value = value($attr, $item)) && media_load($value)) {
        return '<audio src="' . url_media($value) . '" controls="controls"></audio>';
    }

    return '';
}

/**
 * Embed viewer
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function viewer_embed(array $attr, array $item): string
{
    if (($value = value($attr, $item)) && media_load($value)) {
        return '<embed src="' . url_media($value) . '" autoplay="no" loop="no" />';
    }

    return '';
}

/**
 * File viewer
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function viewer_file(array $attr, array $item): string
{
    if (($value = value($attr, $item)) && media_load($value)) {
        return '<a href="' . url_media($value) . '">' . $value . '</a>';
    }

    return '';
}

/**
 * Image viewer
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function viewer_image(array $attr, array $item): string
{
    if (($value = value($attr, $item)) && ($file = media_load($value))) {
        return '<img src="' . image($file, $attr['action']) . '" alt="' . $value . '" />';
    }

    return '';
}

/**
 * Video viewer
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function viewer_video(array $attr, array $item): string
{
    if (($value = value($attr, $item)) && media_load($value)) {
        return '<video src="' . url_media($value) . '" controls="controls"></video>';
    }

    return '';
}
