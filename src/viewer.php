<?php
declare(strict_types = 1);

namespace cms;

/**
 * Viewer
 *
 * @param array $attr
 * @param array $data
 *
 * @return string
 */
function viewer(array $attr, array $data): string
{
    $attr['opt'] = opt($attr);

    if ($attr['viewer']) {
        return $attr['viewer']($attr, $data);
    }

    return $data[$attr['id']] ? encode((string) $data[$attr['id']]) : (string) $data[$attr['id']];
}

/**
 * Option viewer
 *
 * @param array $attr
 * @param array $data
 *
 * @return string
 */
function viewer_opt(array $attr, array $data): string
{
    $result = [];

    foreach ((array) $data[$attr['id']] as $v) {
        if (isset($attr['opt'][$v])) {
            $result[] = $attr['opt'][$v];
        }
    }

    return $result ? encode(implode(', ', $result)) : '';
}

/**
 * Date viewer
 *
 * @param array $attr
 * @param array $data
 *
 * @return string
 */
function viewer_date(array $attr, array $data): string
{
    return $data[$attr['id']] ? date_format(date_create($data[$attr['id']]), data('app', 'date')) : '';
}

/**
 * Datetime viewer
 *
 * @param array $attr
 * @param array $data
 *
 * @return string
 */
function viewer_datetime(array $attr, array $data): string
{
    return $data[$attr['id']] ? date_format(date_create($data[$attr['id']]), data('app', 'datetime')) : '';
}

/**
 * Time viewer
 *
 * @param array $attr
 * @param array $data
 *
 * @return string
 */
function viewer_time(array $attr, array $data): string
{
    return $data[$attr['id']] ? date_format(date_create($data[$attr['id']]), data('app', 'time')) : '';
}

/**
 * Rich text viewer
 *
 * @param array $attr
 * @param array $data
 *
 * @return string
 */
function viewer_rte(array $attr, array $data): string
{
    return (string) $data[$attr['id']];
}

/**
 * File viewer
 *
 * @param array $attr
 * @param array $data
 *
 * @return string
 */
function viewer_file(array $attr, array $data): string
{
    return $data[$attr['id']] ? html('a', ['href' => url_media($data[$attr['id']])], $data[$attr['id']]) : '';
}

/**
 * Image viewer
 *
 * @param array $attr
 * @param array $data
 *
 * @return string
 */
function viewer_image(array $attr, array $data): string
{
    return $data[$attr['id']] ? html('img', ['src' => url_media($data[$attr['id']]), 'alt' => $data[$attr['id']]], null, true) : '';
}

/**
 * Audio viewer
 *
 * @param array $attr
 * @param array $data
 *
 * @return string
 */
function viewer_audio(array $attr, array $data): string
{
    return $data[$attr['id']] ? html('audio', ['src' => url_media($data[$attr['id']]), 'controls' => true]) : '';
}

/**
 * Video viewer
 *
 * @param array $attr
 * @param array $data
 *
 * @return string
 */
function viewer_video(array $attr, array $data): string
{
    return $data[$attr['id']] ? html('video', ['src' => url_media($data[$attr['id']]), 'controls' => true]) : '';
}

/**
 * Filesize viewer
 *
 * @param array $attr
 * @param array $data
 *
 * @return string
 */
function viewer_filesize(array $attr, array $data): string
{
    if (!$data[$attr['id']]) {
        return '';
    }

    if ($data[$attr['id']] < 1000) {
        return $data[$attr['id']] . ' B';
    }

    if ($data[$attr['id']] > 1000000) {
        return round($data[$attr['id']] / 1000000, 1) . ' MB';
    }

    return round($data[$attr['id']] / 1000, 1) . ' kB';
}

/**
 * Position viewer
 *
 * @param array $attr
 * @param array $data
 *
 * @return string
 */
function viewer_pos(array $attr, array $data): string
{
    $parts = explode('.', $data[$attr['id']]);

    foreach ($parts as $k => $v) {
        $parts[$k] = ltrim($v, '0');
    }

    return implode('.', $parts);
}
