<?php
namespace i18n;

use app;
use config;

/**
 * Translate
 *
 * @param string $key
 * @param string ...$params
 *
 * @return string
 */
function translate($key, ...$params)
{
    static $data;

    if ($data === null) {
        $data = array_replace(
            app\data('i18n.' . config\value('i18n.language')),
            app\data('i18n.' . config\value('i18n.locale'))
        );
    }

    if (!$key) {
        return '';
    }

    if (isset($data[$key])) {
        $key = $data[$key];
    }

    if (!$params) {
        return $key;
    }

    return vsprintf($key, $params) ?: $key;
}
