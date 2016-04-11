<?php
namespace akilli;

/**
 * View
 *
 * @return string
 */
function viewer(): string
{
    return '';
}

/**
 * View default
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function viewer_default(array $attr, array $item): string
{
    return viewable($attr) ? encode(value($attr, $item)) : '';
}

/**
 * View file
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function viewer_file(array $attr, array $item): string
{
    if (!viewable($attr)) {
        return '';
    }

    $value = value($attr, $item);

    if (!$value || !($file = media_load($value)) || empty(file_ext($attr['type'])[$file['extension']])) {
        return '';
    }

    $class = 'file-' . $attr['type'] . ' media-' . $attr['action'];
    $config = data('media', $attr['action']);

    if ($config) {
        $style = ' style="max-width:' . $config['width'] . 'px;max-height:' . $config['height'] . 'px;"';
    } else {
        $style = '';
    }

    $url = url_media($value);
    $link = '<a href="' . $url . '" title="' . $value . '" class="' . $class . '">' . $value . '</a>';

    if ($attr['type'] === 'image') {
        return '<img src="' . image($file, $attr['action']) . '" alt="' . $value . '" title="'
            . $value . '" class="' . $class . '" />';
    } elseif ($attr['type'] === 'audio') {
        return '<audio src="' . $url . '" title="' . $value . '" controls="controls" class="' . $class . '"'
            . $style . '>' . $link . '</audio>';
    } elseif ($attr['type'] === 'video') {
        return '<video src="' . $url . '" title="' . $value . '" controls="controls" class="' . $class . '"'
            . $style . '>' . $link . '</video>';
    } elseif ($attr['type'] === 'embed') {
        return '<embed src="' . $url . '" title="' . $value . '" autoplay="no" loop="no" class="' . $class . '"'
            . $style . ' />';
    }

    return $link;
}

/**
 * View datetime
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function viewer_datetime(array $attr, array $item): string
{
    if (!viewable($attr)) {
        return '';
    }

    $code = $attr['id'];
    $format = $attr['frontend'] === 'date' ? config('i18n.date_format') : config('i18n.datetime_format');

    return empty($item[$code]) ? '' : date_format(date_create($item[$code]), $format);
}

/**
 * View rich text editor
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function viewer_rte(array $attr, array $item): string
{
    return viewable($attr) ? value($attr, $item) : '';
}

/**
 * View option
 *
 * @param array $attr
 * @param array $item
 *
 * @return string
 */
function viewer_option(array $attr, array $item): string
{
    if (!viewable($attr)) {
        return '';
    }

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
