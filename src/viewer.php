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
    if (!in_array($attr['context'], $attr['actions'])) {
        return '';
    }

    $attr['opt'] = opt($attr);

    if ($attr['viewer'] && ($call = fqn('viewer_' . $attr['viewer']))) {
        return $call($attr, $item);
    }

    return $item[$attr['uid']] ? encode((string) $item[$attr['uid']]) : (string) $item[$attr['uid']];
}

/**
 * Option viewer
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function viewer_opt(array $attr, array $item): string
{
    if ($attr['opt'] && $item[$attr['uid']]) {
        $values = array_intersect_key($attr['opt'], array_fill_keys((array) $item[$attr['uid']], null));
    }

    return !empty($values) ? encode(implode(', ', $values)) : '';
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
    return $item[$attr['uid']] ? date_format(date_create($item[$attr['uid']]), data('format', 'date.view')) : '';
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
    return $item[$attr['uid']] ? date_format(date_create($item[$attr['uid']]), data('format', 'datetime.view')) : '';
}

/**
 * Time viewer
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function viewer_time(array $attr, array $item): string
{
    return $item[$attr['uid']] ? date_format(date_create($item[$attr['uid']]), data('format', 'time.view')) : '';
}

/**
 * Rich text viewer
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function viewer_rte(array $attr, array $item): string
{
    return (string) $item[$attr['uid']];
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
    return $item[$attr['uid']] ? html_tag('audio', ['src' => url_media($item[$attr['uid']]), 'controls' => true]) : '';
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
    return $item[$attr['uid']] ? html_tag('embed', ['src' => url_media($item[$attr['uid']])], null, true) : '';
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
    return $item[$attr['uid']] ? html_tag('a', ['href' => url_media($item[$attr['uid']])], $item[$attr['uid']]) : '';
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
    return $item[$attr['uid']] ? html_tag('img', ['src' => image($item[$attr['uid']], $attr['context']), 'alt' => $item[$attr['uid']]], null, true) : '';
}

/**
 * Object viewer
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function viewer_object(array $attr, array $item): string
{
    return $item[$attr['uid']] ? html_tag('object', ['data' => url_media($item[$attr['uid']])]) : '';
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
    return $item[$attr['uid']] ? html_tag('video', ['src' => url_media($item[$attr['uid']]), 'controls' => true]) : '';
}
