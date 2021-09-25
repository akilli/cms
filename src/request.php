<?php
declare(strict_types=1);

namespace request;

use app;
use DomainException;

/**
 * Redirect
 */
function redirect(string $url = '/'): never
{
    header('Location: ' . $url);
    exit;
}

/**
 * Filters GET parameters
 */
function getfilter(array $data): array
{
    foreach ($data as $key => $val) {
        if (is_array($val)) {
            $data[$key] = getfilter($val);
            continue;
        }

        $val = trim(preg_replace('#[^\w \-:]#u', '', strip_tags($val)));

        if (is_numeric($val)) {
            $data[$key] += 0;
        } elseif (!$val) {
            unset($data[$key]);
        } else {
            $data[$key] = datetimefilter($val);
        }
    }

    return $data;
}

/**
 * Filters POST parameters
 */
function postfilter(array $data): array
{
    foreach ($data as $key => $val) {
        if (is_array($val)) {
            $data[$key] = postfilter($val);
        } elseif (is_string($val) && $val) {
            $data[$key] = datetimefilter($val);
        }
    }

    return $data;
}

/**
 * Filters date and time values
 */
function datetimefilter(string $val): string
{
    return preg_replace(['#^(\d{4}-\d{2}-\d{2})T(\d{2}:\d{2})$#', '#^(\d{2}:\d{2})$#'], ['$1 $2:00', '$1:00'], $val);
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
            'full_path' => $in['full_path'][$k],
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
