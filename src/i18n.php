<?php
namespace i18n;

use akilli;
use config;

/**
 * Translate
 *
 * @param string $key
 * @param string[] ...$params
 *
 * @return string
 */
function translate(string $key, string ...$params): string
{
    static $data;

    if ($data === null) {
        $data = array_replace(
            akilli\data('i18n.' . config\value('i18n.language')),
            akilli\data('i18n.' . config\value('i18n.locale'))
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
