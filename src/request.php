<?php
declare(strict_types=1);

namespace request;

use DomainException;
use app;

/**
 * Redirect
 */
function redirect(string $url = '/'): void
{
    header('Location: ' . $url);
    exit;
}

/**
 * Filters request parameters
 */
function filter(array $data): array
{
    foreach ($data as $key => $val) {
        if (is_array($val)) {
            $data[$key] = filter($val);
            continue;
        }

        $val = filter_var($val, FILTER_SANITIZE_STRING, FILTER_REQUIRE_SCALAR | FILTER_FLAG_NO_ENCODE_QUOTES);

        if (is_numeric($val)) {
            $data[$key] += 0;
        } elseif (!is_string($val) || !($data[$key] = trim(preg_replace('#[^\w -:]#u', '', $val)))) {
            unset($data[$key]);
        }
    }

    return $data;
}

/**
 * Converts file to post
 *
 * @throws DomainException
 */
function convert(array $in): array
{
    $out = [];

    foreach ($in as $k => $v) {
        if (!is_array($v)) {
            throw new DomainException(app\i18n('Invalid data'));
        }

        $out[$k] = ($keys = array_keys($v)) && sort($keys) && $keys === APP['upload'] ? $v['name'] : convert($v);
    }

    return $out;
}

/**
 * Normalizes array structure and filters file uploads
 */
function normalize(array $in): ?array
{
    if (!($keys = array_keys($in)) || !sort($keys) || $keys !== APP['upload']) {
        return null;
    }

    if (!is_array($in['name'])) {
        if ($in['error'] === UPLOAD_ERR_OK && is_uploaded_file($in['tmp_name'])) {
            return $in;
        }

        if ($in['error'] !== UPLOAD_ERR_NO_FILE) {
            app\msg(app\i18n('Could not upload %s', $in['name']));
        }

        return [];
    }

    $out = [];

    foreach (array_filter($in['name']) as $k => $n) {
        $f = [
            'error' => $in['error'][$k],
            'name' => $n,
            'size' => $in['size'][$k],
            'tmp_name' => $in['tmp_name'][$k],
            'type' => $in['type'][$k],
        ];

        if (is_array($f['name'])) {
            $f = normalize($f);
        } elseif ($f['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($f['tmp_name'])) {
            if ($f['error'] !== UPLOAD_ERR_NO_FILE) {
                app\msg(app\i18n('Could not upload %s', $f['name']));
            }

            continue;
        }

        if ($f) {
            $out[$k] = $f;
        }
    }

    return $out;
}
